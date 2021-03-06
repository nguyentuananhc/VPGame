<?php

use Illuminate\Http\Request;
use App\Customer;
use App\Game;
use App\Item;
use App\Action;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::get('list-users', function(Request $request) {
    $results = Game::orderBy('exp')->get();
    $data = [];
    $level = 1;
    foreach ($results as $user) {
        $exp = $user->exp;
        if ($exp >=1 && $exp <=99) {
            $level=2;
        } elseif ($exp >=100 && $exp <=199) {
            $level=3;
        } elseif ($exp >=200 && $exp <=299) {
            $level=4;
        }elseif ($exp >=300 && $exp <=399) {
            $level=5;
        }else {
            $level=6;
        }
        $obj = new stdClass();
        $obj->name = $user->fullname;
        $obj->lvl = $level;
        $obj->star = $user->star;;

        array_push($data, $obj);
    }
    return response()->json([
        'status'=> 200,
        'message'=> 'Get list user successfully',
        'data'=>$data
    ]);
});

Route::get('user-info/{id}', function(Request $request, $id) {
    $records=Game::where('customer_id', $id)->first();
    $level = 1;
    $exp = $records->exp;
    if ($exp >=1 && $exp <=99) {
        $level=2;
    } elseif ($exp >=100 && $exp <=199) {
        $level=3;
    } elseif ($exp >=200 && $exp <=299) {
        $level=4;
    }elseif ($exp >=300 && $exp <=399) {
        $level=5;
    }else {
        $level=6;
    }
    $records->level = $level;
    if(!$records){
        return response()->json([
            'status'=> 403,
            'message'=> 'Get user informationin error',
            'data'=>$records
        ]);
    }
    return response()->json([
        'status'=> 200,
        'message'=> 'Get user informationin successfully',
        'data'=>$records
    ]);
});

Route::get('list-items', function(Request $request) {
    $results = Item::orderBy('value')->get();
        $data = [];
        foreach ($results as $item) {
            $random = rand($item->min ,$item->max);
            $obj = new stdClass();
            $obj->name = $item->name;
            $obj->value = $item->value;
            $obj->random_value = $random;

            array_push($data, $obj);
        }
    return response()->json([
        'status'=> 200,
        'message'=> 'Get list item successfully',
        'data'=>$data
    ]);
});

Route::get('list-actions', function(Request $request) {
    $results = Action::orderBy('value')->get();
    return response()->json([
        'status'=> 200,
        'message'=> 'Get list item successfully',
        'data'=>$results
    ]);
});

Route::get('leaderboard', function(Request $request) {
    $results = Game::orderBy('exp', 'DESC')
                ->orderBy('star', 'DESC')
                ->take(5)
                ->get();
    $data = [];
    $level = 1;
    foreach ($results as $user) {
        $exp = $user->exp;
        if ($exp >=1 && $exp <=99) {
            $level=2;
        } elseif ($exp >=100 && $exp <=199) {
            $level=3;
        } elseif ($exp >=200 && $exp <=299) {
            $level=4;
        }elseif ($exp >=300 && $exp <=399) {
            $level=5;
        }else {
            $level=6;
        }
        $obj = new stdClass();
        $obj->name = $user->fullname;
        $obj->lvl = $level;
        $obj->star = $user->star;;

        array_push($data, $obj);
    }
    return response()->json([
        'status'=> 200,
        'message'=> 'Get leaderboard successfully',
        'data'=>$data
    ]);
});


Route::post('update-star', function(Request $request) {
    $customer_id = $request->input('customer_id');
    $action = $request->input('action');
    $customer = Game::where('customer_id', $customer_id)->first();

    if(!$customer){
        return response()->json([
            'status'=> 403,
            'message'=> 'Get user informationin error',
            'data'=>$records
        ]);
    }

    if($action === "lucky_wheel"){
        $value = $request->input('value');
        $records = Game::where('customer_id', $customer_id)->first();
        $records->star += $value;
        $records->save();
        return response()->json([
            'status'=> 403,
            'message'=> 'Update star successfully',
            'data'=>$records
        ]);
    }
    $action_type =  $action."_count";
    $action_value = Action::where('name', $action)->value('value');
    $customer->star += $action_value;
    $customer[$action_type] += 1;
    $customer->save();
    return response()->json([
        'status'=> 200,
        'message'=> 'Update star successfully',
        'data'=>$customer
    ]);
});

Route::post('update-exp', function(Request $request) {
    $customer_id = $request->input('customer_id');
    $item_id = $request->input('id');

    $item = Item::where('id', $item_id)->first();
    $random_value = rand($item->min ,$item->max);

    $records = Game::where('customer_id', $customer_id)->first();
    if(!$records){
        return response()->json([
            'status'=> 403,
            'message'=> 'Get user informationin error',
            'data'=>$records
        ]);
    }
    if($records->star - $item->value <= 0) {
        return response()->json([
            'status'=> 400,
            'message'=> 'You dont have enough star to buy this item',
        ]);
    }
    $records->exp += $random_value;
    $records->star -= $item->value;
    $records->save();
    return response()->json([
        'status'=> 200,
        'message'=> 'Update star successfully',
        'data'=>$records
    ]);
});