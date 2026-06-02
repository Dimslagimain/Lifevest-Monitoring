<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\PdfParserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mockery\MockInterface;
use Tests\TestCase;

class PdfScanControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $superadmin;
    private User $admin;
    private User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->superadmin = User::factory()->create(['role' => 'superadmin']);
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->regularUser = User::factory()->create(['role' => 'user']);
        
        Storage::fake('temp_scans');
    }

    public function test_guest_cannot_access_pdf_scan_routes()
    {
        $this->get(route('superadmin.pdf-scan'))->assertRedirect('/login');
        $this->post(route('superadmin.pdf-scan.process'))->assertRedirect('/login');
        $this->get(route('superadmin.pdf-scan.clear'))->assertRedirect('/login');
        $this->post(route('superadmin.pdf-scan.export'))->assertRedirect('/login');
    }

    public function test_non_superadmin_cannot_access_pdf_scan_routes()
    {
        $this->actingAs($this->admin);
        $this->get(route('superadmin.pdf-scan'))->assertStatus(403);

        $this->actingAs($this->regularUser);
        $this->get(route('superadmin.pdf-scan'))->assertStatus(403);
    }

    public function test_superadmin_can_access_pdf_scan_index_without_session()
    {
        $response = $this->actingAs($this->superadmin)->get(route('superadmin.pdf-scan'));
        $response->assertStatus(200);
        $response->assertViewIs('superadmin.pdf-scan');
    }

    public function test_superadmin_can_access_pdf_scan_index_with_session()
    {
        $dummyResult = [
            'rawText' => 'Some raw text',
            'registration' => 'PK-GIA',
            'aircraftType' => 'B777',
            'extractedData' => [['seat_id' => '1A', 'expiry_date' => '2030-01-01', 'registration' => 'PK-GIA']],
            'scanImages' => ['/storage/scan_preview/page_1.jpg']
        ];

        session(['pdf_scan_result' => $dummyResult]);

        $response = $this->actingAs($this->superadmin)->get(route('superadmin.pdf-scan'));
        $response->assertStatus(200);
        $response->assertViewIs('superadmin.pdf-scan-review');
        $response->assertViewHas('registration', 'PK-GIA');
    }

    public function test_clear_scan_clears_session_and_redirects()
    {
        session(['pdf_scan_result' => ['some' => 'data']]);

        $response = $this->actingAs($this->superadmin)->get(route('superadmin.pdf-scan.clear'));
        
        $response->assertRedirect(route('superadmin.pdf-scan'));
        $this->assertNull(session('pdf_scan_result'));
    }

    public function test_scan_requires_file()
    {
        $response = $this->actingAs($this->superadmin)
            ->post(route('superadmin.pdf-scan.process'), []);

        $response->assertSessionHasErrors('file');
    }

    public function test_scan_success_and_active_provider_detection()
    {
        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        $this->mock(PdfParserService::class, function (MockInterface $mock) {
            $mock->shouldReceive('processFile')
                ->once()
                ->andReturn([
                    'registration' => 'PK-GIA',
                    'aircraft_type' => 'B777-300',
                    'seats' => [
                        ['seat_id' => '1A', 'expiry_date' => '2030-01-01', 'registration' => 'PK-GIA']
                    ]
                ]);
        });

        $response = $this->actingAs($this->superadmin)
            ->post(route('superadmin.pdf-scan.process'), [
                'file' => $file
            ]);

        $response->assertStatus(200);
        $response->assertViewIs('superadmin.pdf-scan-review');
        $response->assertViewHas('registration', 'PK-GIA');
        
        // Assert stored in session
        $this->assertNotNull(session('pdf_scan_result'));
        $this->assertEquals('PK-GIA', session('pdf_scan_result.registration'));
    }

    public function test_scan_handles_connection_timeout_gracefully()
    {
        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        $this->mock(PdfParserService::class, function (MockInterface $mock) {
            $mock->shouldReceive('processFile')
                ->once()
                ->andThrow(new \Illuminate\Http\Client\ConnectionException("Connection timeout"));
        });

        $response = $this->actingAs($this->superadmin)
            ->from(route('superadmin.pdf-scan'))
            ->post(route('superadmin.pdf-scan.process'), [
                'file' => $file
            ]);

        $response->assertRedirect(route('superadmin.pdf-scan'));
        $response->assertSessionHas('error');
        $this->assertStringContainsString('Koneksi timeout', session('error'));
    }

    public function test_scan_handles_general_exception_gracefully()
    {
        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        $this->mock(PdfParserService::class, function (MockInterface $mock) {
            $mock->shouldReceive('processFile')
                ->once()
                ->andThrow(new \Exception("API Error (HTTP 429) Rate limit reached"));
        });

        $response = $this->actingAs($this->superadmin)
            ->from(route('superadmin.pdf-scan'))
            ->post(route('superadmin.pdf-scan.process'), [
                'file' => $file
            ]);

        $response->assertRedirect(route('superadmin.pdf-scan'));
        $response->assertSessionHas('error');
        $this->assertStringContainsString('Rate limit tercapai', session('error'));
    }

    public function test_export_excel_validation_and_download()
    {
        $data = [
            [
                'registration' => 'PK-GIA',
                'seat_id' => '1A',
                'expiry_date' => '2030-01-01',
                'confidence' => 0.95,
                'was_corrected' => true,
                'correction_type' => 'corrected',
                'suggestion' => 'Valid input',
                'issue_detected' => 'None'
            ]
        ];

        $response = $this->actingAs($this->superadmin)
            ->post(route('superadmin.pdf-scan.export'), [
                'data' => $data,
                'include_verification' => true,
                'master_registration' => 'PK-GIA',
                'aircraft_type' => 'B777-300'
            ]);

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }
}
