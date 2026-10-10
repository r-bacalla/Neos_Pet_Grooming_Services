<?php

use App\Models\GeneratedReport;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

it('archives all saved report records and preserves their files', function () {
    Storage::fake('local');
    $manager = User::factory()->create(['role' => 'manager']);

    $reports = collect(['reports/first.csv', 'reports/second.csv'])->map(function ($path) use ($manager) {
        Storage::disk('local')->put($path, 'report content');

        return GeneratedReport::create([
            'user_id' => $manager->id,
            'type' => 'sales',
            'title' => 'Sales Report',
            'from_date' => today(),
            'to_date' => today(),
            'file_path' => $path,
        ]);
    });

    $this->actingAs($manager)
        ->get(route('reports.index'))
        ->assertOk()
        ->assertSee('Delete All')
        ->assertSee('Delete');

    $this->actingAs($manager)
        ->delete(route('reports.destroy-all'))
        ->assertRedirect(route('reports.index'))
        ->assertSessionHas('success', 'Moved 2 saved reports to Archive.');

    expect(GeneratedReport::count())->toBe(0)
        ->and(GeneratedReport::onlyTrashed()->count())->toBe(2);
    foreach ($reports as $report) {
        Storage::disk('local')->assertExists($report->file_path);
    }
});

it('shows Archive All on each report page when saved reports exist', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    GeneratedReport::create([
        'user_id' => $manager->id,
        'type' => 'sales',
        'title' => 'Sales Report',
        'from_date' => today(),
        'to_date' => today(),
        'file_path' => 'reports/available.csv',
    ]);

    foreach (['reports.sales', 'reports.products', 'reports.grooming', 'reports.inventory'] as $route) {
        $this->actingAs($manager)
            ->get(route($route))
            ->assertOk()
            ->assertSee('Delete All');
    }
});