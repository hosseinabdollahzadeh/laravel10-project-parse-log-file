<?php

namespace App\Console\Commands;

use DateTime;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ParseLogsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'parse:logs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $logFile = storage_path('logs/logs.txt');
        $handle = fopen($logFile, 'r');

        $batchSize = 1000; // Number of log entries per batch
        $logs = [];

        while (!feof($handle)) {
            $line = fgets($handle);

            // Extract variables using regex patterns
            $regexPattern = '/^(.+?) - \[(.+?)\] "(.+?)" (\d+)/';
            preg_match($regexPattern, $line, $matches);

            // Check if the line matches the expected pattern
            if (count($matches) === 5) {
                $serviceName = $matches[1];
                $timeString = $matches[2];
                $request = $matches[3];
                $responseCode = $matches[4];

                $timeFormat = 'd/M/Y:H:i:s';
                $dateTime = DateTime::createFromFormat($timeFormat, $timeString);

                // Convert the DateTime object to a Unix timestamp
                $timestamp = $dateTime->getTimestamp();


                // Add the log entry to the batch
                $logs[] = [
                    'service_name' => $serviceName,
                    'timestamp' => $timestamp,
                    'request' => $request,
                    'status_code' => $responseCode,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                // Insert the batch into the database when it reaches the desired size
                if (count($logs) >= $batchSize) {
                    DB::table('logs')->insert($logs);
                    $logs = [];
                }
            }
        }

// Insert any remaining logs in the last batch
        if (!empty($logs)) {
            DB::table('logs')->insert($logs);
        }

        fclose($handle);
    }
}
