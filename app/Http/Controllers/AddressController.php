<?php

namespace App\Http\Controllers;

use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AddressController extends Controller
{
    protected $addressService;
    protected $provinceService;

    public function __construct()
    {
        $pathname = storage_path('app/database/sqlite_provinces.sqlite');
        $connection = new \Illuminate\Database\SQLiteConnection(new \PDO('sqlite:' . $pathname));
        $builder = new \Illuminate\Database\Query\Builder($connection);
        $this->addressService = $builder->newQuery()->from('db_postal_code_data');
        $this->provinceService = $builder->newQuery()->from('db_province_data');
    }

    public function getAddressByCode(Request $request)
    {
        $request->validate([
            'postal_code' => 'required|integer',
        ]);
        $address = $this->addressService->where('postal_code', $request->postal_code)->first();
        if (!$address) {
            return response([
                'status' => 'failed',
                'message' => 'Postal code not found',
            ], 404);
        }
        $province = $this->provinceService->where('province_code', $address->province_code)->first();
        $response = [
            'postal_code' => $address->postal_code,
            'province' => Str::title($province->province_name),
            'city' => Str::title($address->city),
            'district' => Str::title($address->sub_district),
            'village' => Str::title($address->urban),
        ];
        return response([
            'status' => 'success',
            'message' => 'Get address by postal code success',
            'data' => $response,
        ], 200);
    }

    public function getProvince()
    {
        $data = [];
        $province = $this->provinceService->orderBy('province_name', 'asc')->get();
        foreach ($province as $prov) {
            $data[] = [
                'province_code' => $prov->province_code,
                'province_name' => Str::title($prov->province_name),
            ];
        }
        return response([
            'status' => 'success',
            'message' => 'Get province success',
            'data' => $data,
        ], 200);
    }

    public function getCityByProvince(Request $request)
    {
        $request->validate([
            'province_code' => 'required|integer',
        ]);
        $city = $this->addressService->where('province_code', $request->province_code)->orderBy('city', 'asc')->get()->pluck('city')->unique()->values();
        if ($city == null) {
            return response([
                'status' => 'failed',
                'message' => 'City not found',
            ], 404);
        }
        $data = [];
        foreach ($city as $cit) {
            $data[] = Str::title($cit);
        }
        return response([
            'status' => 'success',
            'message' => 'Get city by province success',
            'data' => $data,
        ], 200);
    }

    public function getDistrictByCity(Request $request)
    {
        $request->validate([
            'city' => 'required|string',
        ]);
        $district = $this->addressService->where('city', strtoupper($request->city))->orderBy('sub_district', 'asc')->get()->pluck('sub_district')->unique()->values();
        if ($district == null) {
            return response([
                'status' => 'failed',
                'message' => 'District not found',
            ], 404);
        }
        $data = [];
        foreach ($district as $dist) {
            $data[] = Str::title($dist);
        }
        return response([
            'status' => 'success',
            'message' => 'Get district by city success',
            'data' => $data,
        ], 200);
    }

    public function getVillageByDistrict(Request $request)
    {
        $request->validate([
            'district' => 'required|string',
        ]);
        $village = $this->addressService->where('sub_district', strtoupper($request->district))->orderBy('urban')->get()->pluck('urban')->unique()->values();
        if ($village == null) {
            return response([
                'status' => 'failed',
                'message' => 'Village not found',
            ], 404);
        }
        $data = [];
        foreach ($village as $vil) {
            $data[] = Str::title($vil);
        }
        return response([
            'status' => 'success',
            'message' => 'Get village by district success',
            'data' => $data,
        ], 200);
    }

    public function getPostalCodeByVilage(Request $request)
    {
        $request->validate([
            'village' => 'required|string',
            'city' => 'required|string',
        ]);
        $postal_code = $this->addressService->where('city', strtoupper($request->city))->where('urban', strtoupper($request->village))->orderBy('postal_code')->get()->pluck('postal_code')->unique()->values();
        if ($postal_code == null) {
            return response([
                'status' => 'failed',
                'message' => 'Postal code not found',
            ], 404);
        }
        return response([
            'status' => 'success',
            'message' => 'Get postal code by village success',
            'data' => $postal_code,
        ], 200);
    }

    public function addAddress(Request $request)
    {
        $user = $request->user();
        $request->validate([
            'postal_code' => 'required|integer|digits:5',
            'province' => 'required|string',
            'city' => 'required|string',
            'district' => 'required|string',
            'village' => 'required|string',
            'street' => 'required|string',
            'type' => 'required|string',
            'is_primary' => 'nullable|boolean',
        ]);

        if ($request->is_primary == true) {
            Address::where('user_id', $user->id)->where('is_primary', 1)->update(['is_primary' => 0]);
        }
        $address = Address::create([
            'postal_code' => $request->postal_code,
            'province' => $request->province,
            'city' => $request->city,
            'district' => $request->district,
            'village' => $request->village,
            'street' => $request->street,
            'type' => $request->type,
            'is_primary' => $request->is_primary,
            'notes' => $request->notes,
            'user_id' => $user->id,
        ]);

        return response([
            'status' => 'success',
            'message' => 'Add address success',
            'data' => $address,
        ], 201);
    }

    public function getPrimaryAddress()
    {
        $user = request()->user();
        $address = Address::where('user_id', $user->id)->where('is_primary', 1)->first();
        if ($address == null) {
            return response([
                'status' => 'failed',
                'message' => 'Primary address not found',
            ], 404);
        }
        return response([
            'status' => 'success',
            'message' => 'Get primary address success',
            'data' => $address,
        ], 200);
    }

    public function getAddressDetail(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:addresses,id',
        ]);
        $user = $request->user();
        $address = Address::where('user_id', $user->id)->where('id', $request->id)->first();
        if ($address == null) {
            return response([
                'status' => 'failed',
                'message' => 'Address not found',
            ], 404);
        }
        return response([
            'status' => 'success',
            'message' => 'Get address success',
            'data' => $address,
        ], 200);
    }

    public function getAddressPerUser(Request $request)
    {
        $user = $request->user();
        $address = Address::where('user_id', $user->id)->orderBy('is_primary', 'desc')->get();
        if ($address == null) {
            return response([
                'status' => 'failed',
                'message' => 'Address not found',
            ], 404);
        }
        return response([
            'status' => 'success',
            'message' => 'Get address success',
            'data' => $address,
        ], 200);
    }

    public function editAddress(Request $request)
    {
        $user = $request->user();
        $request->validate([
            'id' => 'required|integer|exists:addresses,id',
            'postal_code' => 'required|integer|digits:5',
            'province' => 'required|string',
            'city' => 'required|string',
            'district' => 'required|string',
            'village' => 'required|string',
            'street' => 'required|string',
            'type' => 'required|string',
            'notes' => 'nullable|string',
            'is_primary' => 'nullable|boolean',
        ]);
        $address = Address::where('user_id', $user->id)->where('id', $request->id)->first();
        if ($address == null) {
            return response([
                'status' => 'failed',
                'message' => 'Address not found',
            ], 404);
        }
        if ($request->is_primary == true) {
            Address::where('user_id', $user->id)->where('is_primary', 1)->update(['is_primary' => 0]);
        }
        $address->update($request->all());
        return response([
            'status' => 'success',
            'message' => 'Edit address success',
            'data' => $address,
        ], 200);
    }

    public function deleteAddress(Request $request)
    {
        $user = $request->user();
        $request->validate([
            'id' => 'required|integer|exists:addresses,id',
        ]);
        $address = Address::where('user_id', $user->id)->where('id', $request->id)->first();
        if (!$address) {
            return response([
                'status' => 'failed',
                'message' => 'Address not found',
            ], 404);
        }
        $address->delete();
        
        if($address->is_primary == true) {
            Address::where('user_id', $user->id)->latest()->first()->update(['is_primary' => 1]);
        }

        return response([
            'status' => 'success',
            'message' => 'Delete address success',
        ], 200);
    }
}
