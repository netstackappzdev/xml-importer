<?php

declare(strict_types=1);

namespace App\Lib;

use Exception;
use Google_Client;
use Google_Exception;
use Google_Service_Sheets;
use Google_Service_Sheets_Spreadsheet;

class GoogleSheetsImport {
    /**
     * @var Google_Service_Sheets
     */
    private Google_Service_Sheets $sheets;


    /**
     * GoogleSheetsToArray constructor.
     * @param string $applicationName
     * @param string $authConfig
     * @throws Google_Exception
     */
    public function __construct(
        private string $applicationName, 
        private string $authConfig,
        private array $accessToken
    )
    {
        $client = new Google_Client();
        $client->setAccessType('offline');
        $client->setApplicationName($applicationName);
        $client->setScopes([Google_Service_Sheets::SPREADSHEETS]);
        $client->setAuthConfig($authConfig);$tokenPath = dirname(__FILE__, 3).'/config/google/token.json';
        $client->setAccessToken($accessToken);

        $this->sheets = new Google_Service_Sheets($client);
    }

    /**
     * @param string $title
     * @param array $data
     * @return string
     * @throws Exception
     */
    public function importData(string $title,array $data): string
    {
         // TODO: Assign values to desired properties of `requestBody`:
         $spreadsheet = new Google_Service_Sheets_Spreadsheet([
            'properties' => [
                'title' => $title
            ]
        ]);
        $spreadsheet = $this->sheets->spreadsheets->create($spreadsheet, [
            'fields' => 'spreadsheetId'
        ]);

        $body = new \Google_Service_Sheets_ValueRange([
            'values' =>$data
        ]);
        $params = [
            'valueInputOption' => "USER_ENTERED",
            'insertDataOption' => "INSERT_ROWS"
        ];
        $range = 'Sheet1!A:Z';
        $result = $this->sheets->spreadsheets_values->append($spreadsheet->spreadsheetId, $range, $body, $params);
        return $spreadsheet->spreadsheetId;
    }

}