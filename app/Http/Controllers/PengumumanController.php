<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\Firestore\AnnouncementStore;
use Illuminate\Http\Request;

class PengumumanController extends Controller
{
    public function __construct(private readonly AnnouncementStore $announcements) {}

    public function index(Request $request)
    {
        $page = max(1, (int) $request->input('page', 1));
        $perPage = 15;

        $result = $this->announcements->paginate($perPage, ($page - 1) * $perPage);

        return view('warga.pengumuman.index', [
            'pengumuman' => $result['items'],
            'hasMore' => $result['has_more'],
            'page' => $page,
        ]);
    }

    public function show(string $id)
    {
        $item = $this->announcements->find($id);

        abort_if($item === null, 404);

        return view('warga.pengumuman.show', [
            'item' => $item,
        ]);
    }
}
