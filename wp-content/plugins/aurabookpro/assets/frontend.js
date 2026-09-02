document.addEventListener('DOMContentLoaded', function () {
  const form = document.getElementById('aurabookpro-booking-form');
  if (!form) return;

  const ajaxUrl = (window.aurabookproFrontend && window.aurabookproFrontend.ajax_url)
    || window.ajaxurl
    || '/wp-admin/admin-ajax.php';

  const escapeHtml = (value) => {
    return String(value ?? '').replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  };

  const showSuccessState = (data) => {
    const message = form.querySelector('.aurabookpro-message');
    const submit = form.querySelector('.aurabookpro-submit');

    const summary = data || {};
    const customerName = summary.customer_name || 'Guest';
    const serviceName = summary.service_name || 'Appointment';
    const staffName = summary.staff_name || 'Staff';
    const bookingDate = summary.booking_date || 'Selected date';
    const bookingTime = summary.booking_time || 'Selected time';

    const successMarkup = `
      <div class="aurabookpro-success-card">
        <h3>Booking reserved</h3>
        <div class="aurabookpro-success-meta">
          <div><strong>${escapeHtml(customerName)}</strong></div>
          <div>${escapeHtml(serviceName)}</div>
          <div>${escapeHtml(staffName)}</div>
          <div>${escapeHtml(bookingDate)} at ${escapeHtml(bookingTime)}</div>
        </div>
        ${summary.redirect_url ? `<a class="aurabookpro-checkout-button" href="${summary.redirect_url}">Proceed to checkout</a>` : ''}
      </div>
    `;

    if (message) {
      message.innerHTML = successMarkup;
      message.style.color = '#1d4d3e';
    }

    if (submit) {
      submit.disabled = false;
      submit.textContent = 'Book appointment';
    }

    if (!summary.redirect_url) {
      form.reset();
    }
  };

  form.addEventListener('submit', function (event) {
    event.preventDefault();

    const message = form.querySelector('.aurabookpro-message');
    const submit = form.querySelector('.aurabookpro-submit');
    const formData = new FormData(form);

    formData.append('action', 'aurabookpro_submit_booking');

    if (submit) {
      submit.disabled = true;
      submit.textContent = 'Booking...';
    }

    fetch(ajaxUrl, {
      method: 'POST',
      credentials: 'same-origin',
      body: formData,
    })
      .then((response) => response.json())
      .then((payload) => {
        if (payload && payload.success) {
          const successMessage = payload.data && payload.data.message ? payload.data.message : 'Booking created successfully.';
          if (message) {
            message.innerHTML = '<div class="aurabookpro-success-card"><h3>Booking reserved</h3><div class="aurabookpro-success-meta"><div>' + escapeHtml(successMessage) + '</div></div></div>';
            message.style.color = '#1d4d3e';
          }

          if (payload.data && payload.data.redirect_url) {
            showSuccessState(payload.data);
            return;
          }

          form.reset();
        } else {
          const errorText = payload && payload.data && payload.data.message ? payload.data.message : 'Booking failed.';
          if (message) {
            message.textContent = errorText;
            message.style.color = '#b3261e';
          }
        }
      })
      .catch(() => {
        if (message) {
          message.textContent = 'Booking request failed.';
          message.style.color = '#b3261e';
        }
      })
      .finally(() => {
        if (submit && !form.querySelector('.aurabookpro-success-card')) {
          submit.disabled = false;
          submit.textContent = 'Book appointment';
        }
      });
  });
});
