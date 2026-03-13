<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Training;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class TrainingsController extends Controller
{   
    private bool $storageErrorShown = false;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
    */
    public function index()
    {
        $trainings = Training::with('team')
            ->orderByDesc('status')
            ->orderBy('name')
            ->get();

        return view('backend.trainings.index', [
            'trainings' => $trainings,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
    */
    public function create()
    {
        return view('backend.trainings.new', [
            'isEdit' => false,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
    */
    public function store(Request $request)
    {
        return redirect()->route('trainings.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
    */
    public function show($id)
    {
        $training = Training::with('team')->findOrFail($id);

        if (request()->boolean('modal')) {
            return view('backend.trainings.show-modal', [
                'training' => $training,
            ]);
        }

        return redirect()->route('trainings.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
    */
    public function edit($id)
    {
        return view('backend.trainings.new', [
            'isEdit' => true,
        ]);
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
        return redirect()->route('trainings.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
    */
    public function destroy($id)
    {
        $training = Training::findOrFail($id);
        $this->removeTrainingStorage($training->id, $training->team);
        $training->update(['status' => Training::INACTIVE]);

        return redirect()->route('trainings.index');
    }

    /**
     * Remove the storage directory for a training. This method checks if the provided team ID is valid, and if so, 
     * it constructs the storage path for the training's folder based on the team and training IDs. 
     * It then attempts to delete the directory for the training using Laravel's Storage facade. 
     * If the storage path is not writable or if the deletion fails, it logs an error and flashes an error message to the session to inform the user of the issue. 
     * This method is called when a training is deleted to ensure that any associated files in storage are also removed.
     * @param string $trainingId The ID of the training for which to remove the storage directory. 
     * This should be a UUID string corresponding to the ID of the training being deleted.
     *
    */
    private function removeTrainingStorage(string $trainingId, ?string $teamId): void
    {
        if (empty($teamId)) {
            return;
        }

        $root = storage_path('app/public');

        if (!is_dir($root) || !is_writable($root)) {

            Log::error('Storage path is not writable for trainings.', [
                'path' => $root,
            ]);

            $this->flashStorageError();

            return;
        }

        $disk = Storage::disk('public');
        $path = "teams/{$teamId}/trainings/{$trainingId}";

        if ($disk->exists($path) && !$disk->deleteDirectory($path)) {

            Log::error('Failed to delete training folder.', [
                'training' => $trainingId,
                'team' => $teamId,
                'path' => $path,
            ]);

            $this->flashStorageError();
        }
    }

    /**
     * Flash an error message to the session indicating that there was an issue with storage permissions. 
     * This method ensures that the error message is only flashed once per request to avoid duplicate messages in the session. 
     * It is called whenever there is a failure to delete necessary directories in storage, such as when removing folders for trainings, 
     * to inform the user that some folders could not be deleted due to permission issues.
     * @return void
    */
    private function flashStorageError(): void
    {
        if ($this->storageErrorShown) {
            return;
        }

        $this->storageErrorShown = true;
        session()->flash('error', 'No se pudieron borrar carpetas de entrenamientos. Revisa permisos de storage.');
    }
}
