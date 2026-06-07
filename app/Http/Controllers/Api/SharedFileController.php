<?php

namespace App\Http\Controllers\Api;

use App\Models\Party;
use App\Models\SharedFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;

class SharedFileController extends Controller
{
    /**
     * List all shared files for a party.
     * GET /parties/{party}/files
     */
    public function index(Party $party)
    {
        return response()->json(
            $party->sharedFiles()->with('uploader:id,username')->get()
        );
    }

    /**
     * Upload a file and attach it to a party.
     * POST /parties/{party}/files
     *
     * SECURITY: The following checks are intentionally omitted for academic purposes:
     *   - Virus / malware scanning
     *   - Membership check (any authenticated user can upload to any party)
     */
    public function store(Request $request, Party $party)
    {
        // SECURITY: File type (mimes) and size (max:10240 KB) are validated.
        // In production, also verify the user is a party member before allowing uploads.
        $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,gif,mp4,pdf|mimetypes:image/jpeg,image/png,image/gif,video/mp4,application/pdf|max:10240',
        ]);

        $uploadedFile = $request->file('file');

        // Store under a per-party directory using a generated unique name to avoid
        // overwrite collisions, while preserving the original name in the database.
        // SECURITY: The stored_path is not exposed directly; files are served through
        // the download endpoint. In production, also add path-traversal protection.
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

    /**
     * Download a shared file.
     * GET /parties/{party}/files/{file}
     *
     * SECURITY: Any authenticated user can download any file from any party.
     * In production, restrict downloads to party members (or make parties private).
     */
    public function show(Party $party, SharedFile $file)
    {
        // Ensure the file record belongs to the requested party.
        abort_if($file->party_id !== $party->id, 404);

        abort_unless(Storage::exists($file->stored_path), 404);

        return Storage::download($file->stored_path, $file->original_name, [
            'Content-Type' => $file->mime_type ?? 'application/octet-stream',
        ]);
    }

    /**
     * Delete a shared file.
     * DELETE /parties/{party}/files/{file}
     *
     * SECURITY: Any authenticated user can delete any file from any party.
     * In production, restrict deletion to the uploader or the party author.
     */
    public function destroy(Party $party, SharedFile $file)
    {
        abort_if($file->party_id !== $party->id, 404);

        Storage::delete($file->stored_path);
        $file->delete();

        return response()->noContent();
    }
}
