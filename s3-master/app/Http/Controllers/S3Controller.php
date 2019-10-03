<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use League\Flysystem\FileExistsException;
use League\Flysystem\FileNotFoundException;


class S3Controller extends Controller
{
    public function makeDirectory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'dir' => ['required', 'string']
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => $validator->errors()], 500);
        }

        $S3 = Storage::disk('s3');

        $validate = $S3->exists($request->get('dir'));

        if (!$validate) {
            return response()->json(['status' => false, 'msg' => 'Diretório já existe!'], 401);
        }

        $S3->makeDirectory($request->get('dir'));

        return response()->json(['status' => true], 201);
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimetypes:text/plain,application/pdf,image/png,image/jpeg'],
            'folder' => ['required', 'string']
        ]);

        $file = $request->file('file');
        $folder = $request->input('folder');

        $file->storeAs($folder, $file->getClientOriginalName(), 's3');

        return response()->json(['status' => true], 201);
    }

    public function download(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'filepath' => ['required', 'string']
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => $validator->errors()], 500);
        }

        if (!Storage::disk('s3')->exists($request->get('filepath'))) {
            return response()->json(['status' => 'Arquvio inexistente'], 404);
        }

        $clienteS3 = Storage::disk('s3')->getAdapter()->getClient();

        $comando = $clienteS3->getCommand('GetObject', [
            'Bucket' => env('AWS_BUCKET'),
            'Key' => $request->get('filepath'),
        ]);

        $requisicao = $clienteS3->createPresignedRequest($comando, env('AWS_S3_PRESIGNED_REQUEST_EXPIRES'));

        return response(['link' => (string)$requisicao->getUri()], 200);
    }

    public function move(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'src' => ['required'],
            'dest' => ['required']
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => $validator->errors()], 500);
        }

        $src = $request->input('src');
        $dest = $request->input('dest');

        try {
            $result = Storage::disk('s3')->move($src, $dest);
        } catch (FileExistsException $ex) {
            return response()->json(['status' => false, 'msg' => 'O arquivo ' . '"' . $dest . '"' . ' já existe no diretório de destino.'], 400);
        } catch (FileNotFoundException $ex) {
            return response()->json(['status' => false, 'msg' => 'Arquivo ' . $src . ' não encontrado'], 400);
        }

        return response()->json(['sucesso' => $result], 200);
    }

    public function copy(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'src' => ['required'],
            'dest' => ['required']
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => $validator->errors()], 500);
        }

        $src = $request->input('src');
        $dest = $request->input('dest');

        try {
            $result = Storage::disk('s3')->copy($src, $dest);
        } catch (FileExistsException $ex) {
            return response()->json(['status' => false, 'msg' => 'O arquivo ' . '"' . $dest . '"' . ' já existe no diretório de destino.'], 400);
        } catch (FileNotFoundException $ex) {
            return response()->json(['status' => false, 'msg' => 'Arquivo ' . $src . ' não encontrado'], 400);
        }

        return response()->json(['sucesso' => $result], 200);
    }

    public function delete(Request $request)
    {
        $filePath = trim($request->get('folder'), '/') . '/' . $request->get('filename');
        $filePath = trim($filePath, '/');

        if (!$filePath) {
            return response()->json(['status' => false], 500);
        }

        if (!Storage::disk('s3')->exists($filePath)) {
            return response()->json(['status' => 'Arquvio inexistente'], 404);
        }

        $result = Storage::disk('s3')->delete($filePath);

        return response()->json(['status' => $result, 'msg' => 'Arquivo ' . $filePath . ' removido com sucesso!'], 200);

    }

    public function deleteDirectory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'directory' => ['required']
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => $validator->errors()], 500);
        }

        $dir = $request->input('directory');

        if (!Storage::disk('s3')->exists($dir)) {
            return response()->json(['status' => 'Diretório não encontrado!'], 404);
        }

        $status = Storage::disk('s3')->deleteDirectory($dir);

        return response()->json(['sucesso' => $status], 200);
    }

    public function listFiles(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'path' => ['sometimes'],
            'recursive' => ['sometimes', 'boolean']
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => $validator->errors()], 500);
        }

        if ($request->get('recursive')) {
            $files = Storage::disk('s3')->allFiles('');
        } else {
            $files = Storage::disk('s3')->files($request->get('path'));
        }

        return response()->json(['status' => true, 'files' => $files], 200);
    }

    public function listDir(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'path' => ['sometimes'],
            'recursive' => ['sometimes', 'boolean']
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => $validator->errors()], 500);
        }

        if ($request->get('recursive')) {
            $dirs = Storage::disk('s3')->allDirectories('');
        } else {
            $dirs = Storage::disk('s3')->directories($request->get('path'));
        }

        return response()->json(['status' => true, 'directories' => $dirs], 200);
    }
}
