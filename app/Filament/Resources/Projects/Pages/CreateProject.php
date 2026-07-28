<?php

namespace App\Filament\Resources\Projects\Pages;

use App\Filament\Resources\Projects\ProjectResource;
use Filament\Resources\Pages\CreateRecord;
use App\Models\TicketStatus;

class CreateProject extends CreateRecord
{
    protected static string $resource = ProjectResource::class;

    protected function afterCreate(): void
    {
        $createDefaultStatuses = $this->data['create_default_statuses'] ?? true;

        if ($createDefaultStatuses) {
            $defaultStatuses = [
                ['name' => 'Backlog', 'color' => '#6B7280', 'sort_order' => 0, 'is_completed' => false],
                ['name' => 'To Do', 'color' => '#F59E0B', 'sort_order' => 1, 'is_completed' => false],
                ['name' => 'In Progress', 'color' => '#3B82F6', 'sort_order' => 2, 'is_completed' => false],
                ['name' => 'Review', 'color' => '#8B5CF6', 'sort_order' => 3, 'is_completed' => false],
                ['name' => 'Done', 'color' => '#10B981', 'sort_order' => 4, 'is_completed' => true],
            ];

            foreach ($defaultStatuses as $statusData) {
                $status = new TicketStatus($statusData);
                $status->project_id = $this->record->id;
                $status->save();
            }
        }

        // Ensure the user who created the project is added as a member so they can access it
        if (auth()->check()) {
            $this->record->members()->syncWithoutDetaching(auth()->id());
        }
    }
}
