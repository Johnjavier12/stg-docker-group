<?php

namespace App\Http\Controllers\Hotel\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Booking\Guest;
use Carbon\Carbon;

class InHouseGuestList extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {

        $properties = [];

        if ($request->sands == 'true') {
            $properties[] = 'SANDS';
        }

        if ($request->af == 'true') {
            $properties[] = 'AF';
        }

        $selected_date = Carbon::parse($request->route('date'))->format('Y-m-d');
        
        $inHouse_guests = \App\Models\Booking\Guest::join('bookings', 'guests.booking_reference_number','=','bookings.reference_number')
            ->where(function ($q) use ($selected_date) {
                $q->where('bookings.start_datetime', '=', $selected_date);
                $q->orWhere('bookings.start_datetime', '<=', $selected_date)
                    ->where('bookings.end_datetime', '>=', $selected_date);
                $q->whereIn('bookings.status', ['confirmed', 'pending']);
                $q->whereIn('bookings.type', ['ON', 'GO']);
            })
            ->whereHas('booking.room_reservations_no_filter', function ($q) use ($properties) {
                $q->whereNotIn('status', ['checked_out', 'cancelled', 'blackout', 'voided', 'transferred']);
            })
            ->whereHas('booking.room_reservations_no_filter.room_type.property', function ($q) use ($properties) {
                $q->whereIn('code', $properties);
            })
            ->with(['booking' => function ($q) use ($properties) {
                $q->with(['customer' => function ($q) {
                    $q->select('first_name', 'id', 'last_name', 'middle_name',);
                    $q->with('user:first_name,last_name,email,user_type');
                }]);
                $q->select('id', 'reference_number','type','status', 'customer_id', 'start_datetime', 'end_datetime', 'remarks');
                $q->with(['room_reservations_no_filter' => function ($query) use ($properties) {
                    $query->whereNotIn('status', ['checked_out', 'cancelled', 'blackout', 'voided', 'transferred']);
                    $query->with('room');
                    $query->with('room_type');
                }]);
            }])
            ->whereNull('guests.deleted_at')
            ->select('guests.reference_number', 'guests.booking_reference_number', 'guests.first_name', 'guests.last_name', 'guests.status', 'guests.type', 'guests.age')
            ->get();

        $room_reservations = \App\Models\Hotel\RoomReservation::with(['booking' => function ($q) use ($request) {
            $q->with(['inclusions' => function ($query) {
                $query->where('type', '=', 'room_reservation');
            }]);
            $q->with('customer');
            $q->with('guests');
        }])
        ->whereHas('booking', function ($q) use ($selected_date) {
            $q->whereIn('status', ['confirmed', 'pending']);
            $q->where('bookings.start_datetime', '=', $selected_date);
            $q->orWhere('bookings.start_datetime', '<=', $selected_date)
                ->where('bookings.end_datetime', '>=', $selected_date);
        })
        ->whereHas('booking.room_reservations_no_filter.room_type.property', function ($q) use ($properties) {
            $q->whereIn('code', $properties);
        })
        ->with('room')
        ->whereNotIn('status', ['checked_out','cancelled', 'blackout', 'voided', 'transferred']);

        $booking_refs = collect($room_reservations->get())->pluck('booking_reference_number')->all();

        return [
            'inHouse_guests' => $inHouse_guests,
            'room_reservations' => $room_reservations->get(),
        ];

    }
}
