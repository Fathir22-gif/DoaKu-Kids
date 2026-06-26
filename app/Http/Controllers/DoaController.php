<?php

namespace App\Http\Controllers;

use App\Contracts\DoaServiceInterface;
use App\Contracts\FavoriteServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DoaController extends Controller
{
    protected DoaServiceInterface $doaService;
    protected FavoriteServiceInterface $favoriteService;

    public function __construct(DoaServiceInterface $doaService, FavoriteServiceInterface $favoriteService)
    {
        $this->doaService = $doaService;
        $this->favoriteService = $favoriteService;
    }

    /**
     * Tampilkan halaman utama dengan semua daftar doa & doa acak harian.
     */
    public function index()
    {
        $prayers = $this->doaService->getAllPrayers();
        $randomPrayer = $this->doaService->getRandomPrayer();

        $favoriteIds = [];
        if (Auth::check()) {
            $favoriteIds = $this->favoriteService
                ->getFavoritesByUser(Auth::id())
                ->pluck('prayer_id')
                ->toArray();
        }

        return view('welcome', compact('prayers', 'randomPrayer', 'favoriteIds'));
    }

    /**
     * Tampilkan detail doa berdasarkan ID.
     */
    public function detail(string $id)
    {
        $prayer = $this->doaService->getPrayerById($id);
        if (!$prayer) {
            abort(404, 'Doa tidak ditemukan');
        }

        $isFavorited = false;
        if (Auth::check()) {
            $isFavorited = $this->favoriteService->isFavorited(Auth::id(), $id);
        }

        return view('detail', compact('prayer', 'isFavorited'));
    }

    /**
     * Cari doa berdasarkan nama/kata kunci.
     */
    public function search(Request $request)
    {
        $query = $request->input('query');
        if (empty($query)) {
            return redirect('/');
        }

        $prayers = $this->doaService->searchPrayers($query);

        $favoriteIds = [];
        if (Auth::check()) {
            $favoriteIds = $this->favoriteService
                ->getFavoritesByUser(Auth::id())
                ->pluck('prayer_id')
                ->toArray();
        }

        return view('welcome', [
            'prayers'     => $prayers,
            'randomPrayer' => null,
            'searchQuery' => $query,
            'favoriteIds' => $favoriteIds,
        ]);
    }
}
