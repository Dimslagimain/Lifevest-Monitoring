<?php

/**
 * Aircraft Class Rows Configuration
 * ==================================
 * Defines which rows belong to which class for each layout.
 * Used by AircraftController to determine class_type when saving seats.
 */

return [
    // ═══════════════════════════════════════════════════════════
    // B737 Layouts
    // ═══════════════════════════════════════════════════════════
    'b737-e46' => [
        'business' => range(6, 8),
        'economy' => array_diff(range(21, 46), [24]), // skip 24
    ],
    'b737-e47' => [
        'business' => range(6, 7),
        'economy' => array_diff(range(21, 47), [24]),
    ],
    'b737-e48' => [
        'business' => range(6, 7),
        'economy' => array_diff(range(21, 48), [24]),
    ],
    'b737-e49' => [
        'business' => range(6, 8),
        'economy' => array_diff(range(21, 47), [24]),
    ],

    // ═══════════════════════════════════════════════════════════
    // B777 Layouts
    // ═══════════════════════════════════════════════════════════
    'b777-2class' => [
        'business' => range(6, 12),
        'economy' => array_diff(range(21, 63), [24]),
    ],
    'b777-3class' => [
        'first' => [1, 2],
        'business' => array_diff(range(6, 16), [13]), // skip row 13
        'economy' => array_diff(range(21, 52), [24]),
    ],

    // ═══════════════════════════════════════════════════════════
    // A330-900 Layouts
    // ═══════════════════════════════════════════════════════════
    'a330-900a' => [
        'business' => range(6, 11),
        'economy' => array_diff(range(21, 58), [24]),
    ],
    'a330-900b' => [
        'economy' => array_diff(range(21, 58), [24]),
    ],

    // ═══════════════════════════════════════════════════════════
    // A330-300 Layouts
    // ═══════════════════════════════════════════════════════════
    'a330-300a' => [
        'business' => range(6, 11),
        'economy' => array_diff(range(21, 55), [24]),
    ],
    'a330-300b' => [
        'business' => range(6, 11),
        'economy' => array_diff(range(21, 49), [24]),
    ],
    // A330-300 Layout C (All Economy, rows 21-70)
    'a330-300c' => [
        'economy' => array_diff(range(21, 70), [24]),
    ],
    // A330-300 Cargo (Economy only, rows 21-33, rest is cargo)
    'a330-300cargo' => [
        'economy' => array_diff(range(21, 33), [24]),
    ],

    // ═══════════════════════════════════════════════════════════
    // A330-200 Layouts
    // ═══════════════════════════════════════════════════════════
    'a330-200a' => [
        'business' => range(6, 8),
        'economy' => array_diff(range(21, 52), [24]),
    ],
    'a330-200b' => [
        'business' => range(6, 11),
        'economy' => array_diff(range(21, 45), [24]),
    ],

    // ═══════════════════════════════════════════════════════════
    // ATR72 Layouts
    // ═══════════════════════════════════════════════════════════
    'atr72' => [
        'economy' => array_diff(range(1, 19), [13]),
    ],
];
