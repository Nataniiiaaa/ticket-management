const API = {
  customers: 'api/customers.php',
  ticketsList: 'tickets/list.php',
  ticketsCreate: 'tickets/create.php',
  ticketsUpdate: 'tickets/update.php',
  ticketsDelete: 'tickets/delete.php',
};

let editingId = null;

async function loadCustomerDropdown() {
  const select = document.getElementById('customer_id');
  select.innerHTML = '<option>Loading...</option>'; // loading state
  select.disabled = true;

  try {
    const res = await fetch(`${API.customers}?status=active`);
    const data = await res.json();
    if (!res.ok) throw new Error(data.error || 'Gagal memuat customer');

    const customers = data.data || data; // fleksibel terhadap bentuk response API
    if (!customers.length) {
      select.innerHTML = '<option value="">(Tidak ada customer aktif)</option>'; // empty state
      return;
    }

    select.innerHTML = '<option value="">-- Pilih Customer --</option>';
    customers.forEach((c) => {
      const opt = document.createElement('option');
      opt.value = c.id;
      opt.textContent = c.name; // textContent, BUKAN innerHTML -> aman dari XSS
      select.appendChild(opt);
    });
  } catch (err) {
    select.innerHTML = '<option value="">(Gagal memuat customer)</option>'; // error state
    showMessage('error', 'Customer API error: ' + err.message);
  } finally {
    select.disabled = false;
  }
}

async function loadTickets() {
  const tbody = document.getElementById('ticket-list');
  tbody.innerHTML = '<tr><td colspan="5">Loading...</td></tr>';

  try {
    const res = await fetch(API.ticketsList);
    const data = await res.json();
    if (!res.ok) throw new Error(data.error || 'Gagal memuat ticket');

    if (data.customer_api_warning) {
      showMessage('warning', 'Nama customer mungkin tidak akurat: ' + data.customer_api_warning);
    }

    if (!data.tickets.length) {
      tbody.innerHTML = '<tr><td colspan="5">Belum ada ticket.</td></tr>';
      return;
    }

    tbody.innerHTML = '';
    data.tickets.forEach((t) => {
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>${t.id}</td>
        <td class="subject-cell"></td>
        <td>${t.priority}</td>
        <td>${t.status}</td>
        <td><button data-edit="${t.id}">Edit</button> <button data-delete="${t.id}">Hapus</button></td>
      `;
      // pakai textContent untuk data yang berasal dari user/API -> aman dari XSS
      tr.querySelector('.subject-cell').textContent = `${t.subject} — ${t.customer_name}`;
      tbody.appendChild(tr);
    });
  } catch (err) {
    tbody.innerHTML = `<tr><td colspan="5">Error: ${err.message}</td></tr>`;
  }
}

document.getElementById('ticket-form').addEventListener('submit', async (e) => {
  e.preventDefault();
  const form = e.target;
  const formData = new FormData(form);

  if (editingId) formData.append('id', editingId);
  const url = editingId ? API.ticketsUpdate : API.ticketsCreate;

  try {
    const res = await fetch(url, { method: 'POST', body: formData });
    const data = await res.json();
    if (!res.ok) throw new Error(data.error || 'Gagal menyimpan');

    showMessage('success', editingId ? 'Ticket diupdate.' : 'Ticket dibuat.');
    form.reset();
    editingId = null;
    loadTickets();
  } catch (err) {
    showMessage('error', err.message);
  }
});

document.getElementById('ticket-list').addEventListener('click', async (e) => {
  if (e.target.dataset.delete) {
    if (!confirm('Hapus ticket ini?')) return;
    const formData = new FormData();
    formData.append('id', e.target.dataset.delete);
    await fetch(API.ticketsDelete, { method: 'POST', body: formData });
    loadTickets();
  }

  if (e.target.dataset.edit) {
    const res = await fetch(`${API.ticketsUpdate}?id=${e.target.dataset.edit}`);
    const t = await res.json();
    document.getElementById('customer_id').value = t.customer_id;
    document.getElementById('subject').value = t.subject;
    document.getElementById('description').value = t.description;
    document.getElementById('priority').value = t.priority;
    document.getElementById('status').value = t.status;
    editingId = t.id;
  }
});

function showMessage(type, text) {
  const el = document.getElementById('message');
  el.textContent = text;
  el.className = type;
}

loadCustomerDropdown();
loadTickets();
