<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\PdfParserService;
use ReflectionClass;

class PdfParserServiceTest extends TestCase
{
    private PdfParserService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PdfParserService();
    }

    /**
     * Helper to invoke private/protected methods.
     */
    private function invokeMethod(string $name, array $arguments = [])
    {
        $reflection = new ReflectionClass(PdfParserService::class);
        $method = $reflection->getMethod($name);
        $method->setAccessible(true);
        return $method->invokeArgs($this->service, $arguments);
    }

    public function test_clean_json_removes_unwanted_characters_and_comments()
    {
        $dirtyJson = "/* comment */\n{\n  // line comment\n  \"key\": \"value\",\n}";
        $cleaned = $this->invokeMethod('cleanJson', [$dirtyJson]);

        $this->assertStringNotContainsString('comment', $cleaned);
        $this->assertStringNotContainsString('//', $cleaned);
        // Trailing comma before closing brace should be cleaned
        $this->assertStringNotContainsString(',}', $cleaned);
        
        $decoded = json_decode($cleaned, true);
        $this->assertEquals(['key' => 'value'], $decoded);
    }

    public function test_fix_truncated_json_corrects_brackets_and_quotes()
    {
        // Truncated at a boundary where we have complete inner objects
        $truncated = '{"registration": "PK-GIA", "seats": [{"seat_id": "1A", "expiry_date": "JAN 2030"}]';
        $fixed = $this->invokeMethod('fixTruncatedJson', [$truncated]);

        $decoded = json_decode($fixed, true);
        $this->assertNotNull($decoded);
        $this->assertEquals('PK-GIA', $decoded['registration']);
        $this->assertEquals('1A', $decoded['seats'][0]['seat_id']);
        $this->assertEquals('JAN 2030', $decoded['seats'][0]['expiry_date']);
    }

    public function test_normalize_result_handles_various_formats()
    {
        // Standard array of object-like structures
        $data1 = [
            'registration' => 'PK-GIA',
            'aircraft_type' => 'B777',
            'seats' => [
                ['seat_id' => '1A', 'expiry_date' => '2030-01-01'],
                ['seat_id' => '2B']
            ]
        ];

        $normalized1 = $this->invokeMethod('normalizeResult', [$data1]);
        $this->assertEquals('PK-GIA', $normalized1['registration']);
        $this->assertEquals('B777', $normalized1['aircraft_type']);
        $this->assertCount(2, $normalized1['seats']);
        $this->assertEquals('1A', $normalized1['seats'][0]['seat_id']);
        $this->assertEquals('2B', $normalized1['seats'][1]['seat_id']);
        $this->assertEquals('', $normalized1['seats'][1]['expiry_date']);

        // Simple nested array format: [['1A', '2030-01-01'], ['2B', '']]
        $data2 = [
            'registration' => 'PK-GIA',
            'aircraft_type' => 'B777',
            'seats' => [
                ['1A', '2030-01-01'],
                ['2B', '']
            ]
        ];
        $normalized2 = $this->invokeMethod('normalizeResult', [$data2]);
        $this->assertEquals('1A', $normalized2['seats'][0]['seat_id']);
        $this->assertEquals('2B', $normalized2['seats'][1]['seat_id']);
    }

    public function test_extract_json_finds_valid_json_in_markdown_and_raw_text()
    {
        $content1 = "Here is the response: ```json\n{\"registration\":\"PK-ABC\",\"seats\":[]}\n``` and some footer";
        $extracted1 = $this->invokeMethod('extractJson', [$content1]);
        $this->assertEquals('PK-ABC', $extracted1['registration']);

        $content2 = "Raw json start {\"registration\":\"PK-XYZ\",\"seats\":[]} and some other info";
        $extracted2 = $this->invokeMethod('extractJson', [$content2]);
        $this->assertEquals('PK-XYZ', $extracted2['registration']);
    }
}
