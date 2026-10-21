/* =====================================================================
   MediBook - appointment booking page (patient/appointment.php)

   - clicking a time slot highlights it and updates the summary line
   - "Confirm Booking" is blocked until a free slot is selected
   ===================================================================== */

(function () {
    'use strict';

    var form = document.getElementById('bookingForm');
    if (!form) {
        return;
    }

    var note = document.getElementById('selectedSlotNote');

    function refreshSlots() {
        form.querySelectorAll('.slot-btn').forEach(function (label) {
            var input = label.querySelector('input[type="radio"]');
            if (!input) {
                return;
            }
            // .selected follows the checked radio, .booked is set by PHP
            label.classList.toggle('selected', input.checked && !input.disabled);
        });
    }

    form.querySelectorAll('.slot-btn input[type="radio"]').forEach(function (radio) {
        radio.addEventListener('change', function () {
            refreshSlots();
            if (note) {
                var time = radio.parentNode.textContent.trim();
                note.textContent = 'Selected: ' + time;
            }
        });
    });

    form.addEventListener('submit', function (event) {
        var confirmButton = event.submitter;
        var isBooking = confirmButton && confirmButton.name === 'book_slot';
        if (!isBooking) {
            return; // "Show available slots" always allowed
        }

        var selected = form.querySelector('input[name="start_time"]:checked');
        if (!selected) {
            event.preventDefault();
            if (note) {
                note.textContent = 'Please select a time slot first.';
                note.style.color = '#dc3545';
            }
            return;
        }
        if (note) {
            note.style.color = '';
        }
    });

    refreshSlots();
})();
