#!/usr/bin/env python3
"""
OCR Preprocessing Script for Life Vest Tracker
================================================
Uses OpenCV for image enhancement and Pytesseract for:
1. Orientation & Script Detection (OSD) — auto-rotate pages
2. OCR text extraction — provide text context to AI

Usage:
    python ocr_preprocess.py <image_path1> [image_path2] ... [--output-dir <dir>] [--tesseract-path <path>]
    python ocr_preprocess.py --test

Output:
    JSON to stdout with enhanced image paths, OCR text, orientation info
"""

import sys
import os
import json
import argparse
import math
import traceback

try:
    import cv2
    import numpy as np
    import pytesseract
    from PIL import Image
except ImportError as e:
    print(json.dumps({
        "success": False,
        "error": f"Missing dependency: {e}. Run: pip install opencv-python pytesseract numpy Pillow",
        "enhanced_images": [],
        "ocr_text": "",
        "orientations": []
    }))
    sys.exit(1)


def detect_orientation(image_or_path, confidence_threshold=1.0):
    """
    Use pytesseract.image_to_osd() to detect page orientation.
    Returns rotation angle and confidence.
    """
    try:
        osd_output = pytesseract.image_to_osd(image_or_path, output_type=pytesseract.Output.DICT)
        angle = osd_output.get("rotate", 0)
        confidence = osd_output.get("orientation_conf", 0.0)
        script = osd_output.get("script", "Unknown")
        
        return {
            "angle": int(angle),
            "confidence": float(confidence),
            "script": script,
            "needs_rotation": angle != 0 and confidence >= confidence_threshold
        }
    except Exception as e:
        # OSD can fail on images with very little text or unusual layouts
        return {
            "angle": 0,
            "confidence": 0.0,
            "script": "Unknown",
            "needs_rotation": False,
            "osd_error": str(e)
        }


def rotate_image(img, angle):
    """Rotate image by the detected angle (0, 90, 180, 270)."""
    if angle == 0:
        return img
    elif angle == 90:
        return cv2.rotate(img, cv2.ROTATE_90_COUNTERCLOCKWISE)
    elif angle == 180:
        return cv2.rotate(img, cv2.ROTATE_180)
    elif angle == 270:
        return cv2.rotate(img, cv2.ROTATE_90_CLOCKWISE)
    else:
        # Arbitrary angle rotation
        h, w = img.shape[:2]
        center = (w // 2, h // 2)
        matrix = cv2.getRotationMatrix2D(center, angle, 1.0)
        cos_val = abs(matrix[0, 0])
        sin_val = abs(matrix[0, 1])
        new_w = int(h * sin_val + w * cos_val)
        new_h = int(h * cos_val + w * sin_val)
        matrix[0, 2] += (new_w - w) / 2
        matrix[1, 2] += (new_h - h) / 2
        return cv2.warpAffine(img, matrix, (new_w, new_h), 
                              borderMode=cv2.BORDER_REPLICATE)


def deskew_image(img):
    """
    Detect and correct small skew angles (< 15°) using Hough transform.
    This fixes slight tilts from scanning.
    """
    gray = img if len(img.shape) == 2 else cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
    
    # Edge detection
    edges = cv2.Canny(gray, 50, 150, apertureSize=3)
    
    # Detect lines using Hough transform
    lines = cv2.HoughLinesP(edges, 1, np.pi / 180, threshold=100,
                            minLineLength=100, maxLineGap=10)
    
    if lines is None or len(lines) == 0:
        return img, 0.0
    
    # Calculate angles of detected lines
    angles = []
    for line in lines:
        x1, y1, x2, y2 = line[0]
        if x2 - x1 == 0:
            continue
        angle = math.degrees(math.atan2(y2 - y1, x2 - x1))
        # Only consider near-horizontal lines (within ±15° of horizontal)
        if abs(angle) < 15:
            angles.append(angle)
    
    if not angles:
        return img, 0.0
    
    # Use median angle to avoid outliers
    median_angle = float(np.median(angles))
    
    # Only correct if skew is significant (> 0.3°) but not too large
    if abs(median_angle) < 0.3 or abs(median_angle) > 10:
        return img, 0.0
    
    # Rotate to correct the skew
    corrected = rotate_image(img, median_angle)
    return corrected, median_angle


def enhance_image(img):
    """
    Full image enhancement pipeline for handwritten text on LOPA documents.
    Optimized for Tesseract OCR text extraction.
    
    Steps:
    1. Convert to grayscale
    2. Apply CLAHE (Contrast Limited Adaptive Histogram Equalization)
    3. Bilateral filter (reduce noise while keeping edges)
    4. Grid line removal (remove table borders that confuse Tesseract)
    5. Adaptive thresholding (separate ink from background)
    6. Morphological cleanup (remove small noise)
    7. Sharpen the result
    """
    preprocessing_steps = []
    
    # --- Step 1: Grayscale ---
    if len(img.shape) == 3:
        gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
        preprocessing_steps.append("grayscale")
    else:
        gray = img.copy()
    
    # --- Step 2: CLAHE (Contrast Limited Adaptive Histogram Equalization) ---
    clahe = cv2.createCLAHE(clipLimit=3.0, tileGridSize=(8, 8))
    enhanced = clahe.apply(gray)
    preprocessing_steps.append("clahe_contrast")
    
    # --- Step 3: Bilateral Filter ---
    denoised = cv2.bilateralFilter(enhanced, d=9, sigmaColor=75, sigmaSpace=75)
    preprocessing_steps.append("bilateral_denoise")
    
    # --- Step 3.5: Remove Grid Lines (for Tesseract only) ---
    # Grid lines interfere with character recognition in Tesseract
    no_grid = remove_grid_lines(denoised)
    preprocessing_steps.append("grid_line_removal")
    
    # --- Step 4: Adaptive Thresholding ---
    thresh = cv2.adaptiveThreshold(
        no_grid, 255,
        cv2.ADAPTIVE_THRESH_GAUSSIAN_C,
        cv2.THRESH_BINARY,
        blockSize=15,
        C=8
    )
    preprocessing_steps.append("adaptive_threshold")
    
    # --- Step 5: Morphological Operations ---
    kernel_small = np.ones((2, 2), np.uint8)
    cleaned = cv2.morphologyEx(thresh, cv2.MORPH_OPEN, kernel_small, iterations=1)
    cleaned = cv2.morphologyEx(cleaned, cv2.MORPH_CLOSE, kernel_small, iterations=1)
    preprocessing_steps.append("morphological_cleanup")
    
    # --- Step 6: Sharpening ---
    blurred = cv2.GaussianBlur(cleaned, (0, 0), 3)
    sharpened = cv2.addWeighted(cleaned, 1.5, blurred, -0.5, 0)
    preprocessing_steps.append("sharpen")
    
    return sharpened, preprocessing_steps


def remove_grid_lines(img):
    """
    Remove horizontal and vertical table grid lines from the image.
    Leaves only handwriting and printed text.
    
    This is the single most impactful preprocessing step for AI accuracy:
    grid lines crossing handwriting strokes cause the AI to misread characters
    (e.g. the horizontal line through '0' makes it look like 'Q' or 'D',
    vertical grid lines turn digits into letters, etc.).
    
    Strategy:
    - Detect grid lines using morphological opening with long kernels
    - Subtract grid pixels from the image (replace with white)
    - Dilate the removal mask slightly to clean up anti-aliased line edges
    """
    # Convert to grayscale for line detection
    if len(img.shape) == 3:
        gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
        is_color = True
    else:
        gray = img.copy()
        is_color = False

    # Binarize: dark pixels (ink + grid) become white, background becomes black
    _, binary = cv2.threshold(gray, 0, 255, cv2.THRESH_BINARY_INV + cv2.THRESH_OTSU)

    # Detect horizontal lines: long runs of dark pixels across the page
    # kernel width = 40px minimum line length to qualify as a grid line
    h_kernel_len = max(40, int(gray.shape[1] * 0.03))  # 3% of page width
    h_kernel = cv2.getStructuringElement(cv2.MORPH_RECT, (h_kernel_len, 1))
    horizontal_lines = cv2.morphologyEx(binary, cv2.MORPH_OPEN, h_kernel, iterations=2)

    # Detect vertical lines
    v_kernel_len = max(40, int(gray.shape[0] * 0.03))  # 3% of page height
    v_kernel = cv2.getStructuringElement(cv2.MORPH_RECT, (1, v_kernel_len))
    vertical_lines = cv2.morphologyEx(binary, cv2.MORPH_OPEN, v_kernel, iterations=2)

    # Combine grid mask
    grid_mask = cv2.add(horizontal_lines, vertical_lines)

    # Dilate mask slightly to catch anti-aliased edges of grid lines
    dilate_kernel = cv2.getStructuringElement(cv2.MORPH_RECT, (2, 2))
    grid_mask = cv2.dilate(grid_mask, dilate_kernel, iterations=1)

    # Apply: wherever grid_mask is white (255), set image pixels to white (background)
    if is_color:
        # For color image: set masked pixels to white [255,255,255]
        result = img.copy()
        result[grid_mask == 255] = [255, 255, 255]
    else:
        # For grayscale: set masked pixels to 255 (white)
        result = img.copy()
        result[grid_mask == 255] = 255

    return result


def tile_image(img, num_tiles=3, overlap_px=80):
    """
    Split a page image into horizontal strips (tiles) with overlap.
    
    This is the single most impactful technique for reducing row drift:
    instead of sending one huge image with 40+ rows, the AI only sees
    10-15 rows per tile, making it much harder to lose track of which
    row it's reading.
    
    Args:
        img: Input image (numpy array)
        num_tiles: Number of horizontal strips to create
        overlap_px: Pixels of overlap between adjacent tiles (catches rows at boundaries)
    
    Returns:
        List of tile images (numpy arrays)
    """
    h, w = img.shape[:2]
    tile_height = h // num_tiles
    tiles = []
    
    for i in range(num_tiles):
        y_start = max(0, i * tile_height - overlap_px)
        y_end = min(h, (i + 1) * tile_height + overlap_px)
        
        # Last tile always goes to the bottom
        if i == num_tiles - 1:
            y_end = h
        
        tile = img[y_start:y_end, 0:w].copy()
        tiles.append(tile)
    
    return tiles


def enhance_for_ai(img):
    """
    Create an AI-optimized version of the image.
    IMPORTANT: Does NOT remove grid lines! Claude NEEDS the grid lines
    as visual anchors to distinguish between rows and columns.
    Removing grid lines causes row drift (row 5 data appears in row 6, etc.).
    
    Pipeline:
    1. CLAHE contrast enhancement (LAB color space to preserve hue)
    2. Light denoise
    3. Sharpen
    4. Slight contrast boost
    """
    ai_steps = []

    # Work in color if available (AI benefits from color context)
    if len(img.shape) == 3:
        lab = cv2.cvtColor(img, cv2.COLOR_BGR2LAB)
        l_channel, a_channel, b_channel = cv2.split(lab)

        clahe = cv2.createCLAHE(clipLimit=2.5, tileGridSize=(8, 8))
        l_enhanced = clahe.apply(l_channel)

        enhanced_lab = cv2.merge([l_enhanced, a_channel, b_channel])
        enhanced = cv2.cvtColor(enhanced_lab, cv2.COLOR_LAB2BGR)
        ai_steps.append("clahe_lab_contrast")
    else:
        clahe = cv2.createCLAHE(clipLimit=2.5, tileGridSize=(8, 8))
        enhanced = clahe.apply(img)
        ai_steps.append("clahe_contrast")

    # --- Light denoise (preserve detail) ---
    denoised = cv2.bilateralFilter(enhanced, d=5, sigmaColor=50, sigmaSpace=50)
    ai_steps.append("light_denoise")

    # --- Sharpen ---
    kernel = np.array([
        [0, -1, 0],
        [-1, 5, -1],
        [0, -1, 0]
    ], dtype=np.float32)
    sharpened = cv2.filter2D(denoised, -1, kernel)
    ai_steps.append("sharpen")

    # --- Slight contrast boost ---
    alpha = 1.15
    beta = 5
    adjusted = cv2.convertScaleAbs(sharpened, alpha=alpha, beta=beta)
    ai_steps.append("contrast_boost")

    return adjusted, ai_steps


def extract_ocr_text(image_path, lang="eng"):
    """
    Use pytesseract to extract text from the image.
    Configured for table/form reading with sparse text (individual date cells).
    """
    try:
        # PSM 11 = sparse text: finds as much text as possible without assuming layout.
        # This works much better than PSM 6 for date cells scattered in a table.
        # OEM 3 = LSTM neural net (most accurate for handwriting).
        custom_config = r'--oem 3 --psm 11 -c preserve_interword_spaces=1'

        text = pytesseract.image_to_string(
            image_path,
            lang=lang,
            config=custom_config
        )

        return text.strip()
    except Exception as e:
        return f"[OCR Error: {e}]"


def extract_ocr_with_boxes(image_path, lang="eng"):
    """
    Extract text with bounding box information.
    Useful for mapping text to table cell positions.
    """
    try:
        custom_config = r'--oem 3 --psm 6 -c preserve_interword_spaces=1'
        
        data = pytesseract.image_to_data(
            image_path,
            lang=lang, 
            config=custom_config,
            output_type=pytesseract.Output.DICT
        )
        
        # Build structured result with positions
        results = []
        n_boxes = len(data['text'])
        for i in range(n_boxes):
            text = data['text'][i].strip()
            conf = int(data['conf'][i])
            if text and conf > 0:
                results.append({
                    "text": text,
                    "confidence": conf,
                    "x": data['left'][i],
                    "y": data['top'][i],
                    "w": data['width'][i],
                    "h": data['height'][i],
                    "block": data['block_num'][i],
                    "line": data['line_num'][i],
                })
        
        return results
    except Exception as e:
        return []


def process_image(image_path, output_dir, tesseract_path=None):
    """
    Full preprocessing pipeline for a single image:
    1. Detect orientation (OSD) and auto-rotate
    2. Deskew (fix small tilts)
    3. Enhance for OCR (aggressive - for pytesseract text extraction)
    4. Enhance for AI (moderate - for AI vision model)
    5. Extract OCR text
    """
    if tesseract_path:
        pytesseract.pytesseract.tesseract_cmd = tesseract_path
    
    result = {
        "original_path": image_path,
        "enhanced_image": None,
        "ai_enhanced_image": None,
        "ocr_text": "",
        "ocr_boxes": [],
        "orientation": {},
        "deskew_angle": 0.0,
        "preprocessing_applied": [],
        "errors": []
    }
    
    # Read image
    img = cv2.imread(image_path)
    if img is None:
        result["errors"].append(f"Failed to read image: {image_path}")
        return result
    
    h, w = img.shape[:2]
    all_steps = []
    
    # --- Step 1: Orientation Detection (OSD) ---
    # Resize image to max width 1500px for faster and more accurate OSD detection
    scale_osd = 1500.0 / w if w > 1500 else 1.0
    if scale_osd < 1.0:
        img_osd = cv2.resize(img, (1500, int(h * scale_osd)))
    else:
        img_osd = img.copy()
        
    orientation = detect_orientation(img_osd)
    result["orientation"] = orientation
    
    if orientation.get("needs_rotation", False):
        angle = orientation["angle"]
        img = rotate_image(img, angle)
        all_steps.append(f"rotated_{angle}deg")
        # Update dimensions after rotation
        h, w = img.shape[:2]
    
    # --- Step 2: Deskew ---
    img, deskew_angle = deskew_image(img)
    result["deskew_angle"] = deskew_angle
    if abs(deskew_angle) > 0.3:
        all_steps.append(f"deskewed_{deskew_angle:.1f}deg")
        # Update dimensions after deskew
        h, w = img.shape[:2]
    
    # --- Step 3: Enhance for AI (moderate enhancement, KEEP grid lines) ---
    ai_enhanced, ai_steps = enhance_for_ai(img)
    all_steps.extend([f"ai_{s}" for s in ai_steps])
    
    # Save full AI-enhanced image
    basename = os.path.splitext(os.path.basename(image_path))[0]
    ai_output_path = os.path.join(output_dir, f"{basename}_ai_enhanced.png")
    cv2.imwrite(ai_output_path, ai_enhanced)
    result["ai_enhanced_image"] = ai_output_path
    
    # --- Step 3.5: Create AI tiles (horizontal strips) ---
    # Split the AI-enhanced image into 3 horizontal strips with 80px overlap.
    # This is the KEY technique to prevent row drift: the AI only sees ~15 rows
    # per tile instead of 40+ rows on a full page.
    tiles = tile_image(ai_enhanced, num_tiles=3, overlap_px=80)
    tile_paths = []
    for t_idx, tile in enumerate(tiles):
        tile_path = os.path.join(output_dir, f"{basename}_tile_{t_idx}.png")
        cv2.imwrite(tile_path, tile)
        tile_paths.append(tile_path)
    result["ai_tile_images"] = tile_paths
    all_steps.append(f"tiled_{len(tiles)}_strips")
    
    # --- Step 4: Enhance for OCR (aggressive, with grid line removal for Tesseract) ---
    scale_ocr = 2400.0 / w if w > 2400 else 1.0
    if scale_ocr < 1.0:
        img_ocr = cv2.resize(img, (2400, int(h * scale_ocr)))
    else:
        img_ocr = img.copy()
        
    ocr_enhanced, ocr_steps = enhance_image(img_ocr)
    all_steps.extend([f"ocr_{s}" for s in ocr_steps])
    
    ocr_output_path = os.path.join(output_dir, f"{basename}_ocr_enhanced.png")
    cv2.imwrite(ocr_output_path, ocr_enhanced)
    result["enhanced_image"] = ocr_output_path
    
    # --- Step 5: Extract OCR text ---
    ocr_text = extract_ocr_text(ocr_output_path)
    result["ocr_text"] = ocr_text
    
    # --- Step 6: Extract OCR with bounding boxes ---
    ocr_boxes = extract_ocr_with_boxes(ocr_output_path)
    result["ocr_boxes"] = ocr_boxes
    
    result["preprocessing_applied"] = all_steps
    
    return result


def run_self_test():
    """Quick self-test to verify all dependencies work."""
    test_results = {
        "opencv": False,
        "pytesseract": False,
        "numpy": False,
        "pillow": False,
        "tesseract_version": "",
        "opencv_version": "",
    }
    
    try:
        test_results["opencv"] = True
        test_results["opencv_version"] = cv2.__version__
    except:
        pass
    
    try:
        test_results["numpy"] = True
    except:
        pass
    
    try:
        from PIL import Image as PILImage
        test_results["pillow"] = True
    except:
        pass
    
    try:
        version = pytesseract.get_tesseract_version()
        test_results["pytesseract"] = True
        test_results["tesseract_version"] = str(version)
    except Exception as e:
        test_results["pytesseract_error"] = str(e)
    
    # Create a simple test image and run OCR
    try:
        # 200x50 white image with black text
        test_img = np.ones((50, 200), dtype=np.uint8) * 255
        cv2.putText(test_img, "TEST 2025", (10, 35), 
                    cv2.FONT_HERSHEY_SIMPLEX, 1.0, (0,), 2)
        
        test_path = os.path.join(os.path.dirname(__file__), "_test_ocr.png")
        cv2.imwrite(test_path, test_img)
        
        # Test OSD
        try:
            osd = detect_orientation(test_path)
            test_results["osd_works"] = True
            test_results["osd_result"] = osd
        except:
            test_results["osd_works"] = False
        
        # Test OCR
        text = extract_ocr_text(test_path)
        test_results["ocr_test"] = text
        test_results["ocr_works"] = "TEST" in text.upper() or "2025" in text
        
        # Cleanup
        if os.path.exists(test_path):
            os.remove(test_path)
            
    except Exception as e:
        test_results["test_error"] = str(e)
    
    return test_results


def main():
    parser = argparse.ArgumentParser(description="OCR Preprocessing for Life Vest Tracker")
    parser.add_argument("images", nargs="*", help="Image file paths to process")
    parser.add_argument("--output-dir", "-o", default=None,
                        help="Output directory for enhanced images (default: same as input)")
    parser.add_argument("--tesseract-path", "-t", default=None,
                        help="Path to tesseract executable")
    parser.add_argument("--test", action="store_true",
                        help="Run self-test to verify dependencies")
    parser.add_argument("--ai-only", action="store_true",
                        help="Only output AI-enhanced images (skip OCR extraction)")
    
    args = parser.parse_args()
    
    # Set tesseract path if provided
    if args.tesseract_path:
        pytesseract.pytesseract.tesseract_cmd = args.tesseract_path
    
    # Self-test mode
    if args.test:
        results = run_self_test()
        print(json.dumps({"success": True, "test_results": results}, indent=2))
        sys.exit(0)
    
    # Require at least one image
    if not args.images:
        print(json.dumps({
            "success": False,
            "error": "No image paths provided. Usage: python ocr_preprocess.py <image1> [image2] ...",
            "enhanced_images": [],
            "ocr_text": "",
            "orientations": []
        }))
        sys.exit(1)
    
    # Determine output directory
    output_dir = args.output_dir
    if not output_dir:
        output_dir = os.path.dirname(args.images[0]) or "."
    
    os.makedirs(output_dir, exist_ok=True)
    
    # Process each image
    all_results = []
    all_ocr_texts = []
    all_enhanced_images = []
    all_ai_enhanced_images = []
    all_ai_tile_images = []
    all_orientations = []
    all_errors = []
    all_preprocessing = []
    
    for image_path in args.images:
        if not os.path.exists(image_path):
            all_errors.append(f"File not found: {image_path}")
            continue
        
        try:
            result = process_image(image_path, output_dir, args.tesseract_path)
            all_results.append(result)
            
            if result.get("enhanced_image"):
                all_enhanced_images.append(result["enhanced_image"])
            if result.get("ai_enhanced_image"):
                all_ai_enhanced_images.append(result["ai_enhanced_image"])
            if result.get("ai_tile_images"):
                all_ai_tile_images.extend(result["ai_tile_images"])
            if result.get("ocr_text"):
                all_ocr_texts.append(result["ocr_text"])
            if result.get("orientation"):
                all_orientations.append(result["orientation"])
            if result.get("errors"):
                all_errors.extend(result["errors"])
            if result.get("preprocessing_applied"):
                all_preprocessing.extend(result["preprocessing_applied"])
                
        except Exception as e:
            all_errors.append(f"Error processing {image_path}: {str(e)}")
            traceback.print_exc(file=sys.stderr)
    
    # Combine OCR text from all pages
    combined_ocr = "\n\n--- PAGE BREAK ---\n\n".join(all_ocr_texts)
    
    # Final output
    output = {
        "success": len(all_errors) == 0 or len(all_enhanced_images) > 0,
        "enhanced_images": all_enhanced_images,
        "ai_enhanced_images": all_ai_enhanced_images,
        "ai_tile_images": all_ai_tile_images,
        "ocr_text": combined_ocr,
        "orientations": all_orientations,
        "preprocessing_applied": list(set(all_preprocessing)),
        "pages_processed": len(all_results),
        "errors": all_errors
    }
    
    # Output JSON to stdout (PHP will read this)
    print(json.dumps(output))


if __name__ == "__main__":
    main()
