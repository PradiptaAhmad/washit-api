<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddLaundryRequest;
use App\Http\Requests\DeleteRequest;
use App\Models\Laundry;
use Illuminate\Http\Request;

class LaundryController extends Controller
{
    public function addLaundryServices(AddLaundryRequest $request)
    {
        $request->validated();
        $laundry = Laundry::create([
            'nama_laundry' => $request->nama_laundry,
            'harga' => $request->harga,
            'estimasi_waktu' => $request->estimasi_waktu,
        ]);

        return response([
            'message' => 'Laundry service added successfully',
            'laundry' => $laundry,
        ], 201);
    }

    public function updatePrice(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'harga' => 'required|integer',
        ]);
        $laundry = Laundry::where('id', $request->id)->first();
        if ($laundry == null) {
            return response([
                'message' => 'Laundry service not found',
            ], 404);
        }
        $laundry->update([
            'harga' => $request->harga,
        ]);
        return response([
            'message' => 'Price updated successfully',
            'laundry' => $laundry,
        ], 200);
    }

    public function deleteLaundryService($id)
    {
        $laundry = Laundry::where('id', $id)->first();
        if ($laundry == null) {
            return response([
                'message' => 'Laundry service not found',
            ], 404);
        }
        $laundry->delete();
        return response([
            'message' => 'Laundry service deleted successfully',
        ], 200);
    }

    public function getLaundryServices()
    {
        $laundry = Laundry::all();
        if ($laundry == null) {
            return response([
                'message' => 'Empty laundry services list',
            ], 200);
        } else {
            return response([
                'message' => 'Laundry services list',
                'data' => $laundry,
            ], 200);
        }
    }
}
