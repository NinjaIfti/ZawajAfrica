<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Services\ZohoCampaignService;

class AddUsersToZohoList extends Command
{
    protected $signature = 'zoho:add-users-to-list {listKey}';
    protected $description = 'Add all user emails from the site to a specified Zoho Campaign list';

    public function handle()
    {
        $listKey = $this->argument('listKey');
        $this->info('Fetching users...');
        $users = User::whereNotNull('email')->where('email', '!=', '')->get();
        $this->info('Found ' . $users->count() . ' users.');

        if ($users->isEmpty()) {
            $this->error('No users found.');
            return 1;
        }

        $contacts = [];
        foreach ($users as $user) {
            $contacts[] = [
                'Contact Email' => $user->email,
                'First Name' => $user->name,
                'Last Name' => '',
                'User Type' => $user->subscription_type ?: 'free',
                'Registration Date' => $user->created_at->format('Y-m-d'),
            ];
        }

        $this->info('Adding users to Zoho Campaign list: ' . $listKey);
        $zoho = new ZohoCampaignService();
        $batchSize = 1000;
        $batches = array_chunk($contacts, $batchSize);
        $totalAdded = 0;
        foreach ($batches as $i => $batch) {
            $this->info('Adding batch ' . ($i+1) . ' of ' . count($batches) . '...');
            $result = $zoho->addContactsToList($listKey, $batch);
            if ($result['success']) {
                $this->info('Batch added: ' . count($batch) . ' users.');
                $totalAdded += count($batch);
            } else {
                $this->error('Failed to add batch: ' . ($result['error'] ?? 'Unknown error'));
            }
        }
        $this->info('Done! Total users added: ' . $totalAdded);
        return 0;
    }
} 