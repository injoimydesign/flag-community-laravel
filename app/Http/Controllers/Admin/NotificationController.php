<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Display a listing of notifications.
     */
    public function index(Request $request)
    {
        $query = Notification::with('user');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $notifications = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.notifications.index', compact('notifications'));
    }

    /**
     * Show the form for creating a new notification.
     */
    public function create()
    {
        $users = User::where('role', 'customer')->get();
        return view('admin.notifications.create', compact('users'));
    }

    /**
     * Store a newly created notification.
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'type' => 'required|in:email,sms',
            'subject' => 'required_if:type,email|max:255',
            'message' => 'required',
        ]);

        $notification = Notification::create([
            'user_id' => $request->user_id,
            'type' => $request->type,
            'subject' => $request->subject,
            'message' => $request->message,
            'status' => 'pending',
        ]);

        // Send immediately
        try {
            if ($request->type === 'email') {
                $this->notificationService->sendEmail(
                    $notification->user,
                    $notification->subject,
                    $notification->message
                );
            } else {
                $this->notificationService->sendSMS(
                    $notification->user,
                    $notification->message
                );
            }

            $notification->update([
                'status' => 'sent',
                'sent_at' => now(),
            ]);

            return redirect()->route('admin.notifications.index')
                ->with('success', 'Notification sent successfully.');
        } catch (\Exception $e) {
            $notification->update(['status' => 'failed']);

            return redirect()->route('admin.notifications.index')
                ->with('error', 'Failed to send notification: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified notification.
     */
    public function show(Notification $notification)
    {
        $notification->load('user');
        return view('admin.notifications.show', compact('notification'));
    }

    /**
     * Resend a notification.
     */
    public function resend(Notification $notification)
    {
        try {
            if ($notification->type === 'email') {
                $this->notificationService->sendEmail(
                    $notification->user,
                    $notification->subject,
                    $notification->message
                );
            } else {
                $this->notificationService->sendSMS(
                    $notification->user,
                    $notification->message
                );
            }

            $notification->update([
                'status' => 'sent',
                'sent_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Notification resent successfully.'
            ]);
        } catch (\Exception $e) {
            $notification->update(['status' => 'failed']);

            return response()->json([
                'success' => false,
                'message' => 'Failed to resend notification: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process scheduled notifications.
     */
    public function processScheduled()
    {
        $notifications = Notification::where('status', 'pending')->get();
        $processed = 0;
        $failed = 0;

        foreach ($notifications as $notification) {
            try {
                if ($notification->type === 'email') {
                    $this->notificationService->sendEmail(
                        $notification->user,
                        $notification->subject,
                        $notification->message
                    );
                } else {
                    $this->notificationService->sendSMS(
                        $notification->user,
                        $notification->message
                    );
                }

                $notification->update([
                    'status' => 'sent',
                    'sent_at' => now(),
                ]);
                $processed++;
            } catch (\Exception $e) {
                $notification->update(['status' => 'failed']);
                $failed++;
            }
        }

        return response()->json([
            'success' => true,
            'processed' => $processed,
            'failed' => $failed,
        ]);
    }

    /**
     * Export notifications.
     */
    public function export(Request $request)
    {
        $query = Notification::with('user');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $notifications = $query->get();

        $csv = "ID,User,Type,Subject,Status,Sent At,Created At\n";

        foreach ($notifications as $notification) {
            $csv .= sprintf(
                "%d,%s,%s,%s,%s,%s,%s\n",
                $notification->id,
                $notification->user ? $notification->user->name : 'N/A',
                $notification->type,
                $notification->subject ?? 'N/A',
                $notification->status,
                $notification->sent_at ? $notification->sent_at->format('Y-m-d H:i:s') : 'N/A',
                $notification->created_at->format('Y-m-d H:i:s')
            );
        }

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="notifications-' . date('Y-m-d') . '.csv"');
    }

    /**
     * Get notification metrics.
     */
    public function getMetrics()
    {
        $total = Notification::count();
        $sent = Notification::where('status', 'sent')->count();
        $pending = Notification::where('status', 'pending')->count();
        $failed = Notification::where('status', 'failed')->count();

        $byType = Notification::selectRaw('type, count(*) as count')
            ->groupBy('type')
            ->get();

        return response()->json([
            'total' => $total,
            'sent' => $sent,
            'pending' => $pending,
            'failed' => $failed,
            'by_type' => $byType,
        ]);
    }

    /**
     * Remove the specified notification.
     */
    public function destroy(Notification $notification)
    {
        $notification->delete();

        return redirect()->route('admin.notifications.index')
            ->with('success', 'Notification deleted successfully.');
    }
}
