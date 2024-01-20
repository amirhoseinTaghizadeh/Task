<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\File;
use App\Models\FileEnum;
use App\Services\CompressionService;
use Dotenv\Validator;
use Illuminate\Http\Request;

class CompressionController extends Controller
{

    public function __construct
    (
        private readonly CompressionService $compressionService
    )
    {
    }
    /**
     * Display a listing of the resource.
     */
    public function  compressFile(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all() , [
            'file'      => 'required|file|mimes:pdf,doc,docx|max:10240',
            'type'      => 'required|integer|in:' . implode(',' , [FileEnum::ZIP_FILE , FileEnum::SEVEN_ZIP_FILE , FileEnum::TAR_GZ_FILE]),
        ]);

        if ($validator->fails()){
            return  response()->json(["error"   => $validator->errors()], 400);
        }

        $compressionType    = $request->input("type");
        $originalFileName   = $request->file('file')->getClientOriginalName();

        File::create([
           "original_file_name"     => $originalFileName,
            "type"                  => $compressionType
        ]);

        $filePath           = $request->file('file')->storeAs('uploads', $request->file('file')->getClientOriginalName());

       $exportedFile        = $this->compressionService->compressFile($filePath , $compressionType);
       dd($exportedFile);

       $responseMessageType = match ($compressionType){
           FileEnum::ZIP_FILE           => "zip file",
           FileEnum::SEVEN_ZIP_FILE     => "7zip file",
           FileEnum::TAR_GZ_FILE        => "tar.gz File"
       };

        return  response()->json(["message"     => "file converted to $responseMessageType successfully" , "data"   => $exportedFile]);

    }
}
