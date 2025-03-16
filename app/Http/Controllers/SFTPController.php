<?php

namespace App\Http\Controllers;

use App\Services\SFTPService;
use Illuminate\Http\Request;

class SFTPController extends Controller
{
    protected $sftpService;

    public function __construct(SFTPService $sftpService)
    {
        $this->sftpService = $sftpService;
    }
    public function uploadFile(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx|max:2048', // Ensure it's an Excel file
        ]);

        $file = $request->file('file');
        $filePath = $file->getRealPath();
        $fileName = time() . '_' . $file->getClientOriginalName();

        try {
            $message = $this->sftpService->uploadFile($filePath, $fileName);
            return response()->json(['message' => $message], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
