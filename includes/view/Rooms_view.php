<?php

use Engelsystem\Models\Room;
use Engelsystem\ShiftCalendarRenderer;
use Engelsystem\ShiftsFilterRenderer;

/**
 *
 * @param Room                  $room
 * @param ShiftsFilterRenderer  $shiftsFilterRenderer
 * @param ShiftCalendarRenderer $shiftCalendarRenderer
 * @return string
 */
function Room_view(Room $room, ShiftsFilterRenderer $shiftsFilterRenderer, ShiftCalendarRenderer $shiftCalendarRenderer)
{
    $user = auth()->user();

    $assignNotice = '';
    if (config('signup_requires_arrival') && !$user->state->arrived) {
        $assignNotice = info(render_user_arrived_hint(), true);
    }

    $description = '';
    if ($room->description) {
        $description = '<h3>' . __('Description') . '</h3>';
        $parsedown = new Parsedown();
        $description .= '<div class="well">' . $parsedown->parse($room->description) . '</div>';
    }

    $tabs = [];
    if ($room->map_url) {
        $tabs[__('Map')] = sprintf(
            '<div class="map">'
            . '<iframe style="width: 100%%; min-height: 400px; border: 0 none;" src="%s"></iframe>'
            . '</div>',
            $room->map_url
        );
    }

    $tabs[__('Shifts')] = div('first', [
        $shiftsFilterRenderer->render(page_link_to('rooms', [
            'action'  => 'view',
            'room_id' => $room->id
        ])),
        $shiftCalendarRenderer->render()
    ]);

    $selected_tab = 0;
    $request = request();
    if ($request->has('shifts_filter_day')) {
        $selected_tab = count($tabs) - 1;
    }

    return page_with_title(glyph('map-marker') . $room->name, [
        $assignNotice,
        $description,
        auth()->can('admin_rooms') ? buttons([
            button(
                page_link_to('admin_rooms', ['show' => 'edit', 'id' => $room->id]),
                __('edit'),
                'btn'
            ),
            button(
                page_link_to('admin_rooms', ['show' => 'delete', 'id' => $room->id]),
                __('delete'),
                'btn'
            )
        ]) : '',
        tabs($tabs, $selected_tab),
    ], true);
}

/**
 *
 * @param Room $room
 * @return string
 */
function Room_name_render(Room $room)
{
    if (auth()->can('view_rooms')) {
        return '<a href="' . room_link($room) . '">' . glyph('map-marker') . $room->name . '</a>';
    }

    return glyph('map-marker') . $room->name;
}
