<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    </head>
    <body>
    <style>
        .page-break {
            page-break-after: always;
        }

        .page-break-before {
            page-break-before: always;
        }

        /* @page {
            size: 21cm 29.7cm;
            margin: 30mm 45mm 30mm 45mm;
            /* change the margins as you want them to be. */
        /* } */

    </style>

    <div class="wrapper">
        <div style="text-align: center;"><img src="images/camaya-logo.png" width="140" /></div>
        <h3 style="text-transform: uppercase; text-align: center;">BOOKING STATUS: <span style="color: red">{{$booking['status']}}</span></h3>

        <!-- Booking details -->
        <table border="0" width="100%" style="margin-top: 32px; margin-bottom: 32px;">
                <tr>
                    <td width="20%" rowspan="8"><img src="data:image/png;base64, {!! base64_encode(QrCode::format('svg')->errorCorrection('H')->size(120)->style('dot')->eye('square')->generate($booking['reference_number'])) !!} "></td>
                    <td width="30%">Booking reference no.</td>
                    <td width="40%">{{$booking['reference_number']}}</td>
                </tr>
                <tr>
                    <td>Customer</td>
                    <td>{{$booking['customer']['first_name']}} {{$booking['customer']['last_name']}}</td>
                </tr>
                @if (isset($booking['bookedBy']))
                <tr>
                    <td>Booked by</td>
                    <td>{{$booking['bookedBy']['first_name']}} {{$booking['bookedBy']['last_name']}} ({{$booking['bookedBy']['user_type']}})</td>
                </tr>
                @endif
                <tr>
                    <td>Date of visit</td>
                    <td>
                        {{date('M d, Y', strtotime($booking['start_datetime']))}}
                        {{-- @if ($booking['type'] == 'DT')
                            (Day Tour)
                        @else
                            <span> - {{date('M d, Y', strtotime($booking['end_datetime']))}} (Overnight)</span>
                        @endif --}}
                        @if ($booking['type'] == 'ON')
                            <span> - {{date('M d, Y', strtotime($booking['end_datetime']))}}</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td>Mode of transportation</td>
                    {{-- <td>{{$booking['mode_of_transportation']}}</td> --}}
                    <td>
                        @if ($booking['mode_of_transportation'] == 'own_vehicle')
                            Own Vehicle
                        @elseif ($booking['mode_of_transportation'] == 'camaya_transportation')
                            Camaya Transportation
                        @elseif ($booking['mode_of_transportation'] == 'undecided')
                            Undecided
                        @elseif ($booking['mode_of_transportation'] == 'camaya_vehicle')
                            Camaya Vehicle
                        @elseif ($booking['mode_of_transportation'] == 'van_rental')
                            Van Rental
                        @elseif ($booking['mode_of_transportation'] == 'company_vehicle')
                            Company Vehicle
                        @endif
                    </td>
                </tr>
                {{-- <tr>
                    <td>Estimated time of arrival</td>
                    <td>{{$booking['eta']  ?? '--not set--'}}</td>
                </tr> --}}
                <tr>
                    <td>Total pax</td>
                    <td>{{$booking['adult_pax'] + $booking['kid_pax'] + $booking['infant_pax']}} (Adult:{{$booking['adult_pax']}}, Kid:{{$booking['kid_pax']}}, Infant:{{$booking['infant_pax']}})</td>
                </tr>
        </table>

        {{-- Guest Details --}}
        <h3>GUESTS</h3>
        <table border="0" cellpadding="8" width="100%" style="margin-top: 0px; margin-bottom: 0px;">
                <tr>
                    <th align="left">Name</th>
                    <th align="left">Guest Tag</th>
                    {{-- @if (
                            (isset($booking['bookedBy']) && $booking['bookedBy']['user_type'] == 'agent') ||
                            ($booking['customer']['user_type'] == 'agent')
                        )
                        <th align="left">Guest Tag</th>
                    @endif --}}
                    <th align="left">Age</th>
                    <th align="left">Type</th>
                </tr>
                @foreach ($booking['guests'] as $guest)
                    <tr>
                        <td align="left" style="text-transform: uppercase;">{{$guest['first_name']}} {{$guest['last_name']}}</td>
                        <td align="left" style="text-transform: capitalize;">{{implode(", ", collect($guest['guestTags'])->pluck('name')->all())}}</td>
                        {{-- @if (
                            (isset($booking['bookedBy']) && $booking['bookedBy']['user_type'] == 'agent') ||
                            ($booking['customer']['user_type'] == 'agent')
                        )
                            <td align="left" style="text-transform: capitalize;">{{implode(", ", collect($guest['guestTags'])->pluck('name')->all())}}</td>
                        @endif --}}
                        <td align="left">{{$guest['age']}}</td>
                        <td align="left" style="text-transform: capitalize;">{{$guest['type']}}</td>
                    </tr>
                @endforeach
        </table>

        <div class="page-break-before"></div>

        {{-- Inclusion Details --}}
        <h3>INCLUSIONS</h3>
        <?php

            $packages = collect($booking['inclusions'])
                                    ->where('type', 'package')
                                    ->map(function ($item, $key) {
                                        return [
                                            'name' => $item['item'],
                                            'type' => $item['type'],
                                            'quantity' => $item['quantity'],
                                            'price' => $item['price'],
                                            'discount' => $item['discount'],
                                        ];
                                    })
                                    ->groupBy('name');

        ?>
        @if (count($packages))
        <table border="0" cellpadding="8" width="100%" style="margin-top: 8px; margin-bottom: 0px;">
                <tr>
                    <th align="left">Package</th>
                    <th align="left">Type</th>
                    <th align="right">Quantity</th>
                    <th align="right">Discount</th>
                    <th align="right">Price</th>
                    <th align="right">Total price</th>
                </tr>
                @foreach ($packages as $package => $items)
                    <tr>
                        <td align="left">
                            {{$package}}
                        </td>
                        <td align="left">
                            package
                        </td>
                        <td align="right">
                            {{collect($items)->sum('quantity')}} x
                        </td>
                        <td align="right" style="white-space: nowrap;">
                            P {{number_format(collect($items)->sum('discount'), 2)}}
                        </td>
                        <td align="right" style="white-space: nowrap;">
                            P {{number_format(collect($items)->sum('price'), 2)}} =
                        </td>
                        <td align="right">
                            <div style="white-space: nowrap;">P {{number_format((floatval(collect($items)->sum('price')) - floatval(collect($items)->sum('discount'))) ,2)}}</div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="5">
                            <strong>Promo inclusion:</strong>
                            <?php
                                $packagedetails = \App\Models\Booking\Package::where('name', $package)
                                            ->first();

                                            // echo $packagedetails;
                            ?>

                            <pre style="font-family: 'Tahoma', sans-serif;">{{$packagedetails['description']}}</pre>

                        </td>
                    </tr>
                @endforeach
        </table>
        @endif
        @if (collect($booking['inclusions'])->where('parent_id', '=', null)->where('type', '!=', 'package_inclusion')->where('type', '!=', 'package')->all())
        <table border="0" cellpadding="8" width="100%" style="margin-top: 8px; margin-bottom: 48px; font-size: 14px;">
                <tr>
                    <th align="left">Item</th>
                    <th align="right" style="width: 20px">Qty.</th>
                    <th align="right">Price</th>
                    <th align="right">Discount</th>
                    <th align="right">Total price</th>
                </tr>

                @foreach ($booking['inclusions'] as $inclusion)
                    @if ($inclusion['type'] != 'package_inclusion' && $inclusion['parent_id'] == null && $inclusion['type'] != 'package')
                    <tr>
                        <td align="left">
                            {{$inclusion['parent_id']}}
                            {{$inclusion['item']}}<br/>
                            @if ($inclusion['guestInclusion'] == null)
                                <small></small>
                            @else
                                <small>{{$inclusion['guestInclusion']['first_name']}} {{$inclusion['guestInclusion']['last_name']}}</small>
                            @endif

                        </td>
                        <td align="right">
                            {{$inclusion['quantity']}} x
                        </td>
                        <td align="right" style="white-space: nowrap;">
                            {{number_format($inclusion['price'], 2)}} = P {{number_format($inclusion['price'] * $inclusion['quantity'], 2)}}
                        </td>
                        <td align="right" style="white-space: nowrap;">
                            P {{number_format($inclusion['discount'], 2)}}
                        </td>
                        <td align="right">
                            <div style="white-space: nowrap;">P {{number_format((floatval($inclusion['price']) * intval($inclusion['quantity'])) - floatval($inclusion['discount']),2)}}</div>
                        </td>
                    </tr>
                        @if ($inclusion['type'] == 'package')
                            <tr>
                                <td colspan="5">
                                    Promo inclusion:
                                    <?php
                                        $package = \App\Models\Booking\Package::where('code', $inclusion['code'])
                                                    ->with('packageInclusions.product')
                                                    ->with('packageRoomTypeInclusions.room_type')
                                                    ->first();

                                                    // echo $package->packageRoomTypeInclusions;
                                    ?>

                                    <pre style="font-family: 'Tahoma', sans-serif;">{{$package['description']}}</pre>

                                </td>
                            </tr>
                        @endif
                    @endif
                @endforeach
        </table>
        @endif

        {{-- Invoice Details --}}
        <h3>INVOICES</h3>
        <table border="0" cellpadding="8" width="100%" style="margin-top: 8px; margin-bottom: 48px;">
                <tr>
                    <th align="left">Invoice no.</th>
                    <th align="right">Discount</th>
                    <th align="right">Total</th>
                    <th align="right">Payment</th>
                    <th align="right">Balance</th>
                </tr>
                @foreach ($booking['invoices'] as $item)
                    @if ($item['grand_total'] > 0)
                    <tr>
                        <td align="left" style="white-space: nowrap;">
                            {{$item['reference_number']}}-{{$item['batch_number']}}
                        </td>
                        <td align="right">
                            P {{number_format($item['discount'],2)}}
                        </td>
                        <td align="right">
                            P {{number_format($item['grand_total'],2)}}
                        </td>
                        <td align="right">
                            P {{number_format($item['total_payment'],2)}}
                        </td>
                        <td align="right">
                            P {{number_format($item['balance'],2)}}
                        </td>
                    </tr>
                    @if (count($item['payments']))
                        <tr>
                            <td colspan="6" align="left" style="white-space: nowrap;">
                                <table border="0" cellpadding="4" width="100%" style="margin-top: 8px; margin-bottom: 8px;">
                                    <tr>
                                        <th align="left">Payment ref no.</th>
                                        <th align="left">Mode of payment</th>
                                        <th align="left">Status</th>
                                        <th align="left">Provider</th>
                                        <th align="right">Amount</th>
                                    </tr>
                                    @foreach ($item['payments'] as $payment)
                                        <tr>
                                            <td align="left" style="white-space: nowrap;">
                                                {{$payment['payment_reference_number']}}
                                            </td>
                                            <td align="left">
                                                {{$payment['mode_of_payment']}}
                                            </td>
                                            <td align="left">
                                                {{$payment['status']}}
                                            </td>
                                            <td align="left">
                                                {{$payment['provider']}}
                                            </td>
                                            <td align="right">
                                                P {{number_format($payment['amount'],2)}}
                                            </td>
                                        </tr>
                                    @endforeach
                                </table>
                            </td>
                        </tr>
                    @endif
                    @endif
                @endforeach
                <tr>
                    <td align="right" colspan="6"><strong>Total Balance Amount: P {{number_format(collect($booking['invoices'])->sum('balance'),2) }}</strong></td>
                </tr>
        </table>

        {{-- Vehicle Details --}}
        @if ($booking['mode_of_transportation'] == 'own_vehicle')
        <h3>GUEST VEHICLE</h3>
        <table border="0" cellpadding="8" width="100%" style="margin-top: 8px; margin-bottom: 48px;">
                <tr>
                    <th align="left">Vehicle model</th>
                    <th align="left">Vehicle plate number</th>
                </tr>
                @foreach ($booking['guestVehicles'] as $guest_vehicles)
                    <tr>
                        <td align="left" style="text-transform: uppercase;">{{$guest_vehicles['model']}}</td>
                        <td align="left" style="text-transform: uppercase;">{{$guest_vehicles['plate_number']}}</td>
                    </tr>
                @endforeach
        </table>
        @endif

        @if ($booking['mode_of_transportation'] == 'camaya_transportation')
        <h3>FERRY SCHEDULE</h3>
        <table border="0" cellpadding="8" width="100%" style="margin-top: 8px; margin-bottom: 48px;">
                <tr>
                    <th align="left">Trip #</th>
                    <th align="left">Route</th>
                    <th align="left">Date</th>
                    <th align="left">ETD</th>
                    <th align="left">ETA</th>
                    <th align="left">Transportation</th>
                </tr>
                @if ($camaya_transportations)
                    @foreach ($camaya_transportations as $camaya_transportation)
                        <tr>
                            <td align="left"><small>{{$camaya_transportation['trip_number']}}</small></td>
                            <td align="left"><small>{{$camaya_transportation['route']['origin']['code']}} -&gt; {{$camaya_transportation['route']['destination']['code']}}</small></td>
                            <td align="left"><small>{{$camaya_transportation['trip_date']}}</small></td>
                            <td align="left"><small>{{$camaya_transportation['start_time']}}</small></td>
                            <td align="left"><small>{{$camaya_transportation['end_time']}}</small></td>
                            {{-- <td align="left"><small>{{$camaya_transportation['transportation']['name']}}</small></td> Hide vessel name 03/10/2023 --}}
                            @if ($camaya_transportation['transportation']['type'] === 'ferry')
                            <td align="left"><small>Camaya Ferry</small></td>
                        @elseif ($camata_transporation['transportation']['type'] === 'bus')
                            <td align="left"><small>Camaya Bus</small></td>
                        @endif
                        </tr>
                    @endforeach
                @endif
        </table>
        @endif

    </div>
    </body>
</html>

