/* =====================================================================
   MediBook - shared JavaScript (used on every page)

   1. Reusable form validation
      Any <form data-validate> is checked before it is sent.
      Fields describe their rules, e.g.:
        <input data-label="Email address" data-rules="required|email">
        <input data-label="Password" data-rules="required|minlength:6">
      Supported rules: required, email, minlength:N, maxlength:N,
                       digits:N (numbers only), matches:otherFieldId
   2. Small helpers used by other project scripts
   ===================================================================== */

(function () {
    'use strict';

    /* ---------- 1. Reusable form validation ---------- */

    function showFieldError(field, message) {
        clearFieldError(field);
        field.classList.add('is-invalid');

        var error = document.createElement('span');
        error.className = 'field-error';
        error.textContent = message;
        field.insertAdjacentElement('afterend', error);
    }

    function clearFieldError(field) {
        field.classList.remove('is-invalid');
        var next = field.nextElementSibling;
        if (next && next.classList.contains('field-error')) {
            next.remove();
        }
    }

    function labelOf(field) {
        return field.dataset.label || 'This field';
    }

    function checkOne(field, rules) {
        var value = field.value.trim();

        for (var i = 0; i < rules.length; i++) {
            var parts = rules[i].split(':');
            var rule = parts[0];
            var argument = parts[1] || '';

            if (rule === 'required' && value === '') {
                return labelOf(field) + ' is required.';
            }

            if (rule === 'email' && value !== '' &&
                !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
                return 'Please enter a valid email address.';
            }

            if (rule === 'minlength' && value !== '' &&
                value.length < parseInt(argument, 10)) {
                return labelOf(field) + ' must be at least ' + argument + ' characters.';
            }

            if (rule === 'maxlength' && value.length > parseInt(argument, 10)) {
                return labelOf(field) + ' must be at most ' + argument + ' characters.';
            }

            if (rule === 'digits' && value !== '' && !/^[0-9]+$/.test(value)) {
                return labelOf(field) + ' may only contain numbers.';
            }

            if (rule === 'matches') {
                var other = document.getElementById(argument);
                if (other && value !== other.value.trim()) {
                    return 'The two values do not match.';
                }
            }
        }

        return '';
    }

    function validateForm(form) {
        var fields = form.querySelectorAll('[data-rules]');
        var firstInvalid = null;

        fields.forEach(function (field) {
            var rules = field.dataset.rules.split('|');
            var message = checkOne(field, rules);

            if (message) {
                showFieldError(field, message);
                if (!firstInvalid) {
                    firstInvalid = field;
                }
            } else {
                clearFieldError(field);
            }
        });

        if (firstInvalid) {
            firstInvalid.focus();
            return false;
        }

        return true;
    }

    document.querySelectorAll('form[data-validate]').forEach(function (form) {
        // Use our own messages instead of the browser bubbles
        form.setAttribute('novalidate', 'novalidate');

        form.addEventListener('submit', function (event) {
            if (!validateForm(form)) {
                event.preventDefault();
            }
        });

        // Remove the red outline while the user is fixing a field
        form.querySelectorAll('[data-rules]').forEach(function (field) {
            field.addEventListener('input', function () {
                if (field.classList.contains('is-invalid')) {
                    clearFieldError(field);
                }
            });
        });
    });

    /* ---------- 2. Small helpers ---------- */

    // Auto-dismiss Bootstrap alerts after a few seconds
    window.setTimeout(function () {
        document.querySelectorAll('.alert-dismissible').forEach(function (alert) {
            var dismissButton = alert.querySelector('.btn-close');
            if (dismissButton) {
                dismissButton.click();
            }
        });
    }, 5000);
})();
