<?php

namespace App\Http\Controllers\Back;

use App\{
    Models\Notification,
    Http\Controllers\Controller
};
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Constructor Method.
     *
     * Setting Authentication
     */
    public function __construct()
    {
        $this->middleware('auth:admin');
        $this->middleware('adminlocalize');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function notifications()
    {
        return view('back.notification.index');
    }


    public function view_notification()
    {
        return view('back.notification.notification',[
            'data'=>Notification::orderby('id','desc')
        ]);

    }

    public function read(Request $request, $id)
    {
        $notification = Notification::findOrFail($id);
        $notification->update(['is_read' => 1]);
        $redirect = $this->notificationUrl($notification);

        if ($request->ajax()) {
            return response()->json([
                'count' => Notification::unreadCount(),
                'redirect' => $redirect,
            ]);
        }

        return redirect($redirect);
    }

    public function delete($id)
    {
        Notification::findOrFail($id)->delete();
        return back()->withSuccess(__('Notification Delete Successfully.'));
    }


    /**
     * Clear a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function clear_notf(){
        Notification::where('is_read', 0)->update(['is_read' => 1]);

        if (request()->ajax()) {
            return response()->json([
                'count' => 0,
                'html' => view('back.notification.index')->render(),
            ]);
        }

        return back()->withSuccess(__('Notifications cleared successfully.'));
    }

    private function notificationUrl(Notification $notification)
    {
        if ($notification->user_id) {
            return route('back.user.show', $notification->user_id);
        }

        if ($notification->order_id) {
            return route('back.order.invoice', $notification->order_id);
        }

        return route('back.view.notification');
    }

}
