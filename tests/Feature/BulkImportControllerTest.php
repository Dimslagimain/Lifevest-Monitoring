<?php

namespace Tests\Feature;

use App\Models\Aircraft;
use App\Models\Airline;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class BulkImportControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $superadmin;

    private User $admin;

    private User $regularUser;

    private Airline $airline;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superadmin = User::factory()->create(['role' => 'superadmin']);
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->regularUser = User::factory()->create(['role' => 'user']);
        $this->airline = Airline::create([
            'name' => 'Test Airline',
            'code' => 'TA',
        ]);
    }

    public function test_admin_can_access_bulk_import_page()
    {
        $this->actingAs($this->admin)
            ->get(route('admin.bulk-import'))
            ->assertStatus(200);
    }

    public function test_superadmin_can_access_bulk_import_page()
    {
        $this->actingAs($this->superadmin)
            ->get(route('admin.bulk-import'))
            ->assertStatus(200);
    }

    public function test_regular_user_cannot_access_bulk_import_page()
    {
        $this->actingAs($this->regularUser)
            ->get(route('admin.bulk-import'))
            ->assertStatus(403);
    }

    public function test_download_aircraft_template()
    {
        $this->actingAs($this->admin)
            ->get(route('admin.bulk-import.template', 'aircraft'))
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_download_seat_template()
    {
        $this->actingAs($this->admin)
            ->get(route('admin.bulk-import.template', 'seat'))
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_admin_cannot_download_user_template()
    {
        $this->actingAs($this->admin)
            ->get(route('admin.bulk-import.template', 'user'))
            ->assertStatus(403);
    }

    public function test_superadmin_can_download_user_template()
    {
        $this->actingAs($this->superadmin)
            ->get(route('admin.bulk-import.template', 'user'))
            ->assertStatus(200);
    }

    public function test_download_template_invalid_type_404()
    {
        $this->actingAs($this->admin)
            ->get(route('admin.bulk-import.template', 'invalid'))
            ->assertStatus(404);
    }

    public function test_guest_redirected_on_bulk_import_page()
    {
        $this->get(route('admin.bulk-import'))
            ->assertRedirect(route('login'));
    }

    public function test_guest_redirected_on_bulk_import_process()
    {
        $this->post(route('admin.bulk-import.process'))
            ->assertRedirect(route('login'));
    }

    public function test_import_aircraft_via_excel()
    {
        $file = UploadedFile::fake()->create('aircraft.xlsx', 1024);

        Excel::shouldReceive('import')
            ->once()
            ->andReturnUsing(function ($import) {
                $import->registrations = ['PK-NEW'];
            });

        $this->actingAs($this->admin)
            ->from(route('admin.bulk-import'))
            ->post(route('admin.bulk-import.process'), [
                'import_type' => 'aircraft',
                'file' => $file,
            ])
            ->assertRedirect(route('admin.bulk-import'))
            ->assertSessionHas('success');
    }

    public function test_import_seat_via_excel()
    {
        $file = UploadedFile::fake()->create('seats.xlsx', 1024);

        Aircraft::create([
            'registration' => 'PK-ABC',
            'airline_id' => $this->airline->id,
            'type' => 'B737-800',
            'layout' => 'b737-e46',
            'status' => 'active',
            'pn_adult' => 'PN-001',
            'pn_crew' => 'PN-002',
            'pn_infant' => 'PN-003',
        ]);

        Excel::shouldReceive('import')
            ->once()
            ->andReturnUsing(function ($import) {
                $import->affectedData = [
                    'PK-ABC' => [
                        ['seat_id' => '1A', 'class_type' => 'economy', 'expiry_date' => '2027-01-01'],
                    ],
                ];
            });

        $this->actingAs($this->admin)
            ->from(route('admin.bulk-import'))
            ->post(route('admin.bulk-import.process'), [
                'import_type' => 'seat',
                'file' => $file,
            ])
            ->assertRedirect(route('admin.bulk-import'))
            ->assertSessionHas('success');
    }

    public function test_admin_cannot_import_users()
    {
        $file = UploadedFile::fake()->create('users.xlsx', 1024);

        $this->actingAs($this->admin)
            ->post(route('admin.bulk-import.process'), [
                'import_type' => 'user',
                'file' => $file,
            ])
            ->assertStatus(403);
    }

    public function test_superadmin_can_import_users()
    {
        $file = UploadedFile::fake()->create('users.xlsx', 1024);

        Excel::shouldReceive('import')
            ->once()
            ->andReturnNull();

        $this->actingAs($this->superadmin)
            ->from(route('admin.bulk-import'))
            ->post(route('admin.bulk-import.process'), [
                'import_type' => 'user',
                'file' => $file,
            ])
            ->assertRedirect(route('admin.bulk-import'))
            ->assertSessionHas('success');
    }

    public function test_import_requires_file()
    {
        $this->actingAs($this->admin)
            ->post(route('admin.bulk-import.process'), [
                'import_type' => 'aircraft',
            ])
            ->assertSessionHasErrors('file');
    }
}
