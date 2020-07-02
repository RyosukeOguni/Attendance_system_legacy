<?php

namespace App\Http\Controllers;

use App\Note;
use App\User;
use App\School;
use Carbon\Carbon;
use App\Performance;
use app\Library\BaseClass;
use Illuminate\Http\Request;
use App\Http\Requests\PerformanceRequest;
use Illuminate\Support\Facades\Validator;

class PerformanceController extends Controller
{
    // 実績記録一覧表示
    public function index(Request $request)
    {
        // 所属校で実績記録の表示を絞る
        if ($request->has('school_id')) {
            $school_id = $request->school_id;
        } else {
            $school_id = 1;
        }

        // 日付で実績記録の表示を絞る
        if ($request->has('date')) {
            $date = $request->date;
        } else {
            $date = Carbon::now()->toDateString();
        }

        $records = Performance::dateIdEqual($date)
            // ->has('user')
            ->whereHas('User', function ($q) use ($school_id) {
                $q->where('school_id', $school_id);
            })
            ->with(['user', 'note'])->paginate(10);


        $param = [
            'records' => $records,
            'schoolselect' => BaseClass::schoolSelect(),
            'school_id' =>  $school_id,
            'date' => $date,
        ];
        return view('admin.performance_index', $param);
    }

    // 実績記録登録画面
    public function register(Request $request)
    {
        if ($request->has('id')) {
            $user_id = $request->id;
        } else {
            $user_id = null;
        }

        if ($request->has('date')) {
            $date = $request->date;
        } else {
            $date = BaseClass::toDayDate();
        }

        $return_url = url()->previous();

        $param = [
            'user_id' => $user_id,
            'date' => $date,
            'return_url' => $return_url,
            'userslist' => BaseClass::usersList(),
            'noteslist' => BaseClass::notesList(),
            'timetable' => BaseClass::timeTable(),
        ];
        return view('admin.performance_register', $param);
    }

    // 実績記録登録処理
    public function store(PerformanceRequest $request)
    {
        // すべてのリクエスト内容を取得
        $form = $request->all();
        // リクエスト内容から不要な '_token'を取り除く
        unset($form['_token']);
        unset($form['url']);
        // Modelクラスを生成して、Form内容を一括（fill）で入力し、DBに保存（save）する
        $record = new Performance();
        $record->fill($form)->save();
        $title = '登録完了';

        $return_url =  $request->url;

        $param = [
            'record' => $record,
            'title' => $title,
            'return_url' => $return_url,
        ];
        return view('admin.performance_successful', $param);
    }

    // 実績記録変更画面
    public function edit(Request $request)
    {
        // クエリから指定したidのレコードを取得
        $record = Performance::where('id', $request->id)->first();

        // food_fgフラグに真偽値を入力
        if (!empty($record->food_fg)) {
            $food_fg = true;
        } else {
            $food_fg = false;
        }

        // outside_fgフラグに真偽値を入力
        if (!empty($record->outside_fg)) {
            $outside_fg = true;
        } else {
            $outside_fg = false;
        }

        // medical_fgフラグに真偽値を入力
        if (!empty($record->medical_fg)) {
            $medical_fg = true;
        } else {
            $medical_fg = false;
        }

        $return_url =  url()->previous();

        $param = [
            'record' => $record,
            'food_fg' => $food_fg,
            'outside_fg' => $outside_fg,
            'medical_fg' => $medical_fg,
            'return_url' => $return_url,
            'userslist' => BaseClass::usersList(),
            'noteslist' => BaseClass::notesList(),
            'timetable' => BaseClass::timeTable(),
        ];
        return view('admin.performance_edit', $param);
    }

    // 実績記録変更画面
    public function update(PerformanceRequest $request)
    {
        $record = Performance::where('id', $request->id)->first();
        // すべてのリクエスト内容を取得
        $form = $request->all();
        // リクエスト内容から不要な '_token'を取り除く
        unset($form['_token']);
        unset($form['url']);
        $record->fill($form)->save();
        $title = '変更完了';

        $return_url =  $request->url;

        $param = [
            'record' => $record,
            'title' => $title,
            'return_url' => $return_url,
        ];

        return view('admin.performance_successful', $param);
    }


    // 実績記録情報削除
    public function delete(Request $request)
    {
        // クエリのUserIDのレコードをデリート
        $record = Performance::where('id', $request->id)->first();
        $record->delete();
        $title = '削除完了';

        $return_url =  $request->url;

        $param = [
            'record' => $record,
            'title' => $title,
            'return_url' => $return_url,
        ];
        return view('admin.performance_successful', $param);
    }
}
