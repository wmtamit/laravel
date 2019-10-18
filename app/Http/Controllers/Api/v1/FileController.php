<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\File;
use App\Rules\AntiXssFinder;
use App\Traits\GetUuid;
use App\Utils\AppConstant;
use App\Traits\ApiResponse;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class FileController extends Controller
{
    use GetUuid;
    use ApiResponse;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $files = File::all();

            /*  Return user  */
            $this->setData('status', AppConstant::STATUS_OK);
            $this->setData('message', __('File data.'));
            $this->setData("files", $files);
            return response()->json($this->setResponse(), AppConstant::CREATED);

        } catch (QueryException $e) {
            DB::rollBack();
            $this->setData('status', AppConstant::STATUS_FAIL);
            $this->setData('message', __('auth.server_error'));
            return response()->json($this->setResponse(), AppConstant::INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['bail', 'required', 'min:3', 'max:30', new AntiXssFinder()],
            'file_path' => ['bail', 'required', 'max:5000', 'mimes:xlsx,csv'],
        ]);

        if ($validator->fails()) {
            $this->setData('status', AppConstant::STATUS_FAIL);
            $this->setData('message', $validator->messages()->first());
            return response()->json($this->setResponse(), AppConstant::UNPROCESSABLE_REQUEST);
        }

        try {
            $file_store = new File();
            $file_store->name = $request->name;
            $file_store->uuid = $this->uuid();

            $file_data = $request->file('file_path');
            $uuid = $file_store->uuid;
            if ($request->has('file_path')) {
                $fileName = $file_data->getClientOriginalName();
                $file_store->file_path = $file_data->storeAs('fileUpload/' . $uuid, $fileName);
            }
            $file_store->save();

            /*  Return user  */
            $this->setData('status', AppConstant::STATUS_OK);
            $this->setData('message', __('File saved successfully.'));
            $this->setData("files", $file_store);
            return response()->json($this->setResponse(), AppConstant::CREATED);

        } catch (QueryException $e) {
            DB::rollBack();
            $this->setData('status', AppConstant::STATUS_FAIL);
            $this->setData('message', __('auth.server_error'));
            return response()->json($this->setResponse(), AppConstant::INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $file = File::find($id);
            $data = [];

            if ($file) {
                $data['key1'] = $file->name;
                $data['key2'] = $file;
                $data['key3'] = array($file->id, $file->uuid, $file->name, $file->file_path);

                $this->setData('status', AppConstant::STATUS_OK);
                $this->setData('message', __('File retrieve successfully.'));
                $this->setData("files", $data);
            } else {
                $this->setData('status', AppConstant::STATUS_OK);
                $this->setData('message', __('Record not available.'));
            }
            return response()->json($this->setResponse(), AppConstant::CREATED);
        } catch (QueryException $e) {
            DB::rollBack();
            $this->setData('status', AppConstant::STATUS_FAIL);
            $this->setData('message', __('auth.server_error'));
            return response()->json($this->setResponse(), AppConstant::INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['bail', 'required', 'min:3', 'max:30', new AntiXssFinder()],
            'file_path' => ['bail', 'required', 'max:5000', 'mimes:xlsx,csv'],
        ]);

        if ($validator->fails()) {
            $this->setData('status', AppConstant::STATUS_FAIL);
            $this->setData('message', $validator->messages()->first());
            return response()->json($this->setResponse(), AppConstant::UNPROCESSABLE_REQUEST);
        }

        try {
            $fileData = File::find($id);
            $dataArray = [];

            if ($fileData) {
                $uuid = $fileData->uuid;
                if ($request->has('file_path')) {
                    $file_data = $request->file('file_path');
                    $path = 'fileUpload/' . $fileData->uuid;
                    if (Storage::disk(config('filesystems.default'))->exists($path)) {
                        Storage::deleteDirectory($path);
                    }
                    $file_name = $file_data->getClientOriginalName();
                    $dataArray['file_path'] = $file_data->storeAs('fileUpload/' . $uuid, $file_name);
                }
                $dataArray['name'] = $request->name;
                File::where('uuid', $uuid)->update($dataArray);
                $file = File::where('uuid', $uuid)->first();

                $this->setData('status', AppConstant::STATUS_OK);
                $this->setData('message', __('File updated successfully.'));
                $this->setData('file', $file);
//                $this->setData("files", $file);
            } else {
                $this->setData('status', AppConstant::STATUS_OK);
                $this->setData('message', __('Record not available.'));
            }
            return response()->json($this->setResponse(), AppConstant::CREATED);
        } catch (QueryException $e) {
            DB::rollBack();
            $this->setData('status', AppConstant::STATUS_FAIL);
            $this->setData('message', __('auth.server_error'));
            return response()->json($this->setResponse(), AppConstant::INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $fileData = File::find($id);
            if ($fileData) {
                $path = 'fileUpload/' . $fileData->uuid;
                if (Storage::disk(config('filesystems.default'))->exists($path)) {
                    Storage::deleteDirectory($path);
                }

                File::where('id', $id)->delete();
                $this->setData('status', AppConstant::STATUS_OK);
                $this->setData('message', __('File deleted successfully.'));
            } else {
                $this->setData('status', AppConstant::STATUS_OK);
                $this->setData('message', __('Record not available.'));
            }
            return response()->json($this->setResponse(), AppConstant::CREATED);
        } catch (QueryException $e) {
            DB::rollBack();
            $this->setData('status', AppConstant::STATUS_FAIL);
            $this->setData('message', __('auth.server_error'));
            return response()->json($this->setResponse(), AppConstant::INTERNAL_SERVER_ERROR);
        }
    }
}
