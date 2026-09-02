import axios from 'axios';
import ApexCharts from 'apexcharts';
import * as bootstrap from 'bootstrap';
import DataTable from 'datatables.net-bs5';
import 'datatables.net-responsive-bs5';
import 'datatables.net-buttons-bs5';
import Dropzone from 'dropzone';
import $ from 'jquery';
import JSZip from 'jszip';
import pdfMake from 'pdfmake/build/pdfmake';
import pdfFonts from 'pdfmake/build/vfs_fonts';
import select2 from 'select2';
import Swal from 'sweetalert2';

window.$ = window.jQuery = $;
window.axios = axios;
window.ApexCharts = ApexCharts;
window.bootstrap = bootstrap;
window.DataTable = DataTable;
window.Dropzone = Dropzone;
window.JSZip = JSZip;
window.Swal = Swal;

pdfMake.vfs = pdfFonts.vfs;
select2($);

const sidebar = document.getElementById('sidebar');
const content = document.getElementById('content');
const topbar = document.getElementById('topbar');
const toggleBtn = document.getElementById('toggleBtn');
const mobileBtn = document.getElementById('mobileBtn');
const overlay = document.getElementById('overlay');
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

if (csrfToken) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
}

axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

const reloadAdminTables = () => {
    document.querySelectorAll('[data-admin-table]').forEach((table) => {
        if (table._dtInstance) {
            table._dtInstance.ajax.reload(null, false);
        }
    });
};

document.querySelectorAll('[data-admin-table]').forEach((table) => {
    const columns = JSON.parse(table.dataset.columns || '[]');

    table._dtInstance = new DataTable(table, {
        ajax: table.dataset.ajaxUrl,
        columns,
        deferRender: true,
        processing: true,
        responsive: true,
        serverSide: true,
        pageLength: Number(table.dataset.pageLength || 10),
        order: [[0, 'desc']],
        columnDefs: [
            {
                targets: 'no-sort',
                orderable: false,
                searchable: false,
            },
        ],
    });

    if (table.dataset.reloadMs) {
        window.setInterval(() => table._dtInstance.ajax.reload(null, false), Number(table.dataset.reloadMs));
    }
});

document.addEventListener('click', async (event) => {
    const button = event.target.closest('[data-table-action]');

    if (!button) {
        return;
    }

    event.preventDefault();

    const needsReason = button.dataset.prompt === 'reason';
    const promptResult = await Swal.fire({
        title: button.dataset.title || 'Are you sure?',
        text: button.dataset.text || 'This action will update the selected record.',
        icon: needsReason ? 'warning' : 'question',
        input: needsReason ? 'textarea' : undefined,
        inputPlaceholder: needsReason ? 'Write rejection reason' : undefined,
        showCancelButton: true,
        confirmButtonColor: '#c60000',
        confirmButtonText: button.dataset.confirm || 'Continue',
        preConfirm: (value) => {
            if (needsReason && !value) {
                Swal.showValidationMessage('Reason is required.');
            }

            return value;
        },
    });

    if (!promptResult.isConfirmed) {
        return;
    }

    const payload = new FormData();

    if (needsReason) {
        payload.append('rejection_reason', promptResult.value);
    }

    if (button.dataset.status) {
        payload.append('status', button.dataset.status);
    }

    if (button.dataset.notes) {
        payload.append('notes', button.dataset.notes);
    }

    const method = button.dataset.method || 'post';

    try {
        if (method === 'delete') {
            await axios.delete(button.dataset.actionUrl);
        } else {
            await axios.post(button.dataset.actionUrl, payload);
        }

        await Swal.fire({
            title: method === 'delete' ? 'Deleted' : 'Updated',
            text: method === 'delete' ? 'Record has been removed.' : 'Data has been refreshed.',
            icon: 'success',
            timer: 1400,
            showConfirmButton: false,
        });

        reloadAdminTables();
    } catch (error) {
        const message = error.response?.data?.message || 'The action could not be completed.';

        await Swal.fire({
            title: 'Failed',
            text: message,
            icon: 'error',
            confirmButtonColor: '#c60000',
        });
    }
});

if (toggleBtn) {
    toggleBtn.addEventListener('click', () => {
        sidebar?.classList.toggle('collapsed');
        content?.classList.toggle('full');
        topbar?.classList.toggle('full');
    });
}

if (mobileBtn) {
    mobileBtn.addEventListener('click', () => {
        sidebar?.classList.add('mobile-show');
        overlay?.classList.add('show');
    });
}

overlay?.addEventListener('click', () => {
    sidebar?.classList.remove('mobile-show');
    overlay.classList.remove('show');
});

$('.js-select2').select2({ width: '100%' });
