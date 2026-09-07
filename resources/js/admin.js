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
import './motion';

window.$ = window.jQuery = $;
window.axios = axios;
window.ApexCharts = ApexCharts;
window.bootstrap = bootstrap;
window.DataTable = DataTable;
window.Dropzone = Dropzone;
window.JSZip = JSZip;
window.Swal = Swal;

pdfMake.addVirtualFileSystem(pdfFonts);
select2(window, $);

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
    columns.forEach((column) => {
        if (column.data === 'actions') {
            column.responsivePriority = 1;
        }
    });

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

const paymentUploadMessage = (response) => {
    if (typeof response === 'string') {
        return response;
    }

    if (response?.errors) {
        return Object.values(response.errors).flat().shift() || 'The payment proof could not be uploaded.';
    }

    return response?.message || 'The payment proof could not be uploaded.';
};

document.querySelectorAll('[data-payment-dropzone]').forEach((dropzoneElement) => {
    const form = dropzoneElement.closest('form');
    const fallbackInput = form?.querySelector('[data-payment-proof-fallback]');
    const submitButton = form?.querySelector('[data-payment-submit]');

    if (!form || dropzoneElement.dropzone) {
        return;
    }

    fallbackInput?.removeAttribute('required');
    fallbackInput?.removeAttribute('name');
    fallbackInput?.classList.add('d-none');

    const defaultSubmitHtml = submitButton?.innerHTML;
    const paymentDropzone = new Dropzone(dropzoneElement, {
        acceptedFiles: 'application/pdf,image/jpeg,image/png,image/webp,.pdf,.jpg,.jpeg,.png,.webp',
        addRemoveLinks: true,
        autoProcessQueue: false,
        dictDefaultMessage: 'Drop payment proof here',
        dictMaxFilesExceeded: 'Only one proof file can be uploaded.',
        dictRemoveFile: 'Remove file',
        headers: {
            Accept: 'application/json',
            ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}),
        },
        maxFiles: 1,
        maxFilesize: 4,
        method: form.getAttribute('method') || 'post',
        paramName: 'proof_file',
        parallelUploads: 1,
        url: form.getAttribute('action'),
    });

    paymentDropzone.on('addedfile', () => {
        if (paymentDropzone.files.length > 1) {
            paymentDropzone.removeFile(paymentDropzone.files[0]);
        }
    });

    paymentDropzone.on('sending', (file, xhr, formData) => {
        formData.append('_token', csrfToken || form.querySelector('[name="_token"]')?.value || '');
        formData.append('paid_at', form.querySelector('[name="paid_at"]')?.value || '');
        formData.append('notes', form.querySelector('[name="notes"]')?.value || '');

        if (submitButton) {
            submitButton.disabled = true;
            submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>Uploading';
        }
    });

    paymentDropzone.on('success', (file, response) => {
        window.location.href = response?.redirect_url || window.location.href;
    });

    paymentDropzone.on('error', async (file, response) => {
        await Swal.fire({
            title: 'Upload failed',
            text: paymentUploadMessage(response),
            icon: 'error',
            confirmButtonColor: '#c60000',
        });
    });

    paymentDropzone.on('complete', () => {
        if (submitButton) {
            submitButton.disabled = false;
            submitButton.innerHTML = defaultSubmitHtml;
        }
    });

    form.addEventListener('submit', (event) => {
        if (!form.matches('[data-payment-form]')) {
            return;
        }

        event.preventDefault();
        event.stopPropagation();

        if (paymentDropzone.getQueuedFiles().length === 0) {
            Swal.fire({
                title: 'Proof file required',
                text: 'Please choose a payment proof file before submitting.',
                icon: 'warning',
                confirmButtonColor: '#c60000',
            });

            return;
        }

        paymentDropzone.processQueue();
    });
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

document.querySelectorAll('[data-coauthors]').forEach((container) => {
    const list = container.querySelector('[data-coauthor-list]');
    const template = container.querySelector('[data-coauthor-template]');
    const addButton = container.querySelector('[data-add-coauthor]');
    let nextIndex = list.children.length;

    const syncRows = () => {
        addButton.disabled = list.children.length >= 10;
        list.querySelectorAll('[data-coauthor-row]').forEach((row) => {
            const hasAuthorDetails = Array.from(row.querySelectorAll('input:not([type="checkbox"])')).some((input) => input.value.trim() !== '');
            row.querySelector('[data-coauthor-email]').required = hasAuthorDetails || row.querySelector('[data-coauthor-participant]').checked;
        });
    };

    addButton.addEventListener('click', () => {
        if (list.children.length >= 10) {
            return;
        }
        list.insertAdjacentHTML('beforeend', template.innerHTML.replaceAll('__INDEX__', String(nextIndex++)));
        $(list.lastElementChild).find('.js-select2').select2({ width: '100%' });
        list.lastElementChild.querySelector('input').focus();
        syncRows();
    });

    list.addEventListener('click', (event) => {
        const removeButton = event.target.closest('[data-remove-coauthor]');
        if (removeButton) {
            const row = removeButton.closest('[data-coauthor-row]');
            $(row).find('.js-select2').select2('destroy');
            row.remove();
            syncRows();
        }
    });
    list.addEventListener('change', syncRows);
    list.addEventListener('input', syncRows);
    syncRows();
});

const attendanceQr = document.querySelector('[data-attendance-qr]');
if (attendanceQr) {
    import('qrcode').then(({ default: QRCode }) => {
        return QRCode.toCanvas(attendanceQr, attendanceQr.dataset.attendanceQr, { width: 420, margin: 4, errorCorrectionLevel: 'M' });
    }).catch(() => {
        document.querySelector('[data-qr-status]').textContent = 'Could not display the QR. Please refresh and generate it again.';
    });
}

const attendanceScanner = document.querySelector('[data-attendance-scanner]');
if (attendanceScanner?.querySelector('[data-camera-start]')) {
    const startButton = attendanceScanner.querySelector('[data-camera-start]');
    const stopButton = attendanceScanner.querySelector('[data-camera-stop]');
    const video = attendanceScanner.querySelector('[data-camera-video]');
    const preview = attendanceScanner.querySelector('[data-camera-preview]');
    const status = attendanceScanner.querySelector('[data-camera-status]');
    let scanner = null;
    let scanning = false;
    let submitting = false;

    const stopCamera = () => {
        scanning = false;
        scanner?.destroy();
        scanner = null;
        preview.hidden = true;
        stopButton.hidden = true;
        startButton.disabled = submitting;
    };

    startButton.addEventListener('click', async () => {
        if (!window.isSecureContext || !navigator.mediaDevices?.getUserMedia) {
            status.textContent = 'Camera access requires HTTPS or localhost. Open this site using a secure connection.';
            return;
        }
        startButton.disabled = true;
        stopButton.hidden = false;
        scanning = true;
        status.textContent = 'Allow camera access, then point the camera at the committee QR.';
        try {
            const { default: QrScanner } = await import('qr-scanner');
            if (!scanning) {
                return;
            }
            preview.hidden = false;
            scanner = new QrScanner(video, async (result) => {
                if (submitting || !scanning) {
                    return;
                }
                submitting = true;
                stopCamera();
                status.textContent = 'QR detected. Recording attendance…';
                try {
                    const { data } = await axios.post(attendanceScanner.dataset.scanUrl, { code: result.data });
                    status.textContent = data.message;
                    window.setTimeout(() => { window.location.assign(data.redirect_url); }, 1000);
                } catch (error) {
                    const errors = error.response?.data?.errors;
                    status.textContent = errors ? Object.values(errors).flat().join(' ') : 'Could not record attendance. Check your connection and try again.';
                    submitting = false;
                    startButton.disabled = false;
                }
            }, { preferredCamera: 'environment', highlightScanRegion: true, highlightCodeOutline: true, returnDetailedScanResult: true });
            await scanner.start();
        } catch (error) {
            stopCamera();
            status.textContent = 'Could not open the camera. Allow camera permission in your browser, close other camera apps, and try again.';
        }
    });
    stopButton.addEventListener('click', () => {
        stopCamera();
        status.textContent = 'Camera stopped. Select Open Camera to scan again.';
    });
    window.addEventListener('pagehide', stopCamera);
    document.addEventListener('visibilitychange', () => {
        if (document.hidden && scanning) {
            stopCamera();
            status.textContent = 'Camera paused. Select Open Camera to continue.';
        }
    });
}

document.querySelectorAll('[data-admin-review-form]').forEach((form) => {
    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        const button = form.querySelector('button[type="submit"], button:not([type])');
        button.disabled = true;
        try {
            const { data } = await axios.post(form.action, new FormData(form));
            await Swal.fire({ icon: 'success', title: data.message });
            window.location.reload();
        } catch (error) {
            const errors = error.response?.data?.errors;
            await Swal.fire({ icon: 'error', title: 'Could not save', text: errors ? Object.values(errors).flat().join(' ') : error.response?.data?.message || 'Please try again.' });
            button.disabled = false;
        }
    });
});

document.addEventListener('click', async (event) => {
    const button = event.target.closest('[data-document-url]');
    if (!button) {
        return;
    }
    button.disabled = true;
    try {
        const { data } = await axios.get(button.dataset.documentUrl);
        await pdfMake.createPdf(data.definition).download(data.filename);
    } catch (error) {
        await Swal.fire({ icon: 'error', title: 'Download failed', text: error.response?.data?.message || 'Could not generate the PDF. Please try again.' });
    } finally {
        button.disabled = false;
    }
});
