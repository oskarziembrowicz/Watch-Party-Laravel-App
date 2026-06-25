<?php

namespace App\Http\Controllers\Api;

use App\Models\Party;
use App\Models\SharedFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;

class SharedFileController extends Controller
{
    public function index(Party $party)
    {
        return response()->json(
            $party->sharedFiles()->with('uploader:id,username')->get()
        );
    }

    public function store(Request $request, Party $party)
    {
        $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,gif,mp4,pdf|mimetypes:image/jpeg,image/png,image/gif,video/mp4,application/pdf|max:10240',
        ]);

        $uploadedFile = $request->file('file');

        // Store under a per-party directory using a generated unique name to avoid
        // overwrite collisions, while preserving the original name in the database.
        $storedPath = $uploadedFile->store("shared_files/{$party->id}");

        $sharedFile = SharedFile::create([
            'party_id'      => $party->id,
            'uploaded_by'   => $request->user()?->id,
            'original_name' => $uploadedFile->getClientOriginalName(),
            'stored_path'   => $storedPath,
            'mime_type'     => $uploadedFile->getMimeType(),
            'size'          => $uploadedFile->getSize(),
        ]);

        return response()->json($sharedFile->load('uploader:id,username'), 201);
    }

    public function show(Party $party, SharedFile $file)
    {
        // Ensure the file record belongs to the requested party.
        abort_if($file->party_id !== $party->id, 404);

        abort_unless(Storage::exists($file->stored_path), 404);

        return Storage::download($file->stored_path, $file->original_name, [
            'Content-Type' => $file->mime_type ?? 'application/octet-stream',
        ]);
    }

    public function destroy(Party $party, SharedFile $file)
    {
        abort_if($file->party_id !== $party->id, 404);

        Storage::delete($file->stored_path);
        $file->delete();

        return response()->noContent();
    }
}
