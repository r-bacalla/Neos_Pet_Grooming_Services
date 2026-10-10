import Papa from 'papaparse';
import { readSheet } from 'read-excel-file/browser';

const form = document.querySelector('[data-inventory-import]');

if (form) {
    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        const fileInput = form.querySelector('input[type="file"]');
        const status = form.querySelector('[data-import-status]');
        const submit = form.querySelector('button[type="submit"]');
        const file = fileInput.files[0];

        if (!file) {
            status.textContent = 'Choose an Excel or CSV file first.';
            return;
        }

        submit.disabled = true;
        status.textContent = 'Reading spreadsheet...';

        try {
            let sourceRows;
            if (file.name.toLowerCase().endsWith('.csv')) {
                const parsed = Papa.parse(file, {
                    header: true,
                    skipEmptyLines: 'greedy',
                });
                if (parsed.errors.length) {
                    throw new Error(parsed.errors[0].message);
                }
                sourceRows = parsed.data;
            } else {
                const sheet = await readSheet(file);
                const [headers = [], ...values] = sheet;
                sourceRows = values.map((row) => Object.fromEntries(
                    headers.map((header, index) => [header, row[index] ?? ''])
                ));
            }

            const normalizeKey = (key) => String(key).toLowerCase().replace(/[^a-z0-9]/g, '');
            const aliases = {
                name: ['productname', 'name'],
                sku: ['sku', 'productsku'],
                category: ['category'],
                cost: ['cost', 'unitcost', 'costprice'],
                price: ['price', 'sellingprice', 'unitprice'],
                stock: ['stock', 'quantity', 'currentstock', 'onhand'],
                low_stock_level: ['lowstocklevel', 'lowstockwarninglevel', 'reorderlevel'],
                expiry_date: ['expirydate', 'expirationdate', 'expiry'],
            };

            const rows = sourceRows.map((source) => {
                const normalized = Object.fromEntries(
                    Object.entries(source).map(([key, value]) => [normalizeKey(key), value])
                );
                const get = (field) => {
                    for (const key of aliases[field]) {
                        if (normalized[key] !== undefined) return normalized[key];
                    }
                    return '';
                };
                const number = (value, fallback = '') => {
                    if (value === '' || value === null || value === undefined) return fallback;
                    const result = Number(String(value).replace(/[^0-9.-]/g, ''));
                    return Number.isFinite(result) ? result : value;
                };
                const expiry = get('expiry_date');

                return {
                    name: String(get('name')).trim(),
                    sku: String(get('sku')).trim(),
                    category: String(get('category')).trim(),
                    cost: number(get('cost')),
                    price: number(get('price')),
                    stock: number(get('stock')),
                    low_stock_level: number(get('low_stock_level'), 5),
                    expiry_date: expiry instanceof Date ? expiry.toISOString().slice(0, 10) : String(expiry).trim(),
                };
            }).filter((row) => Object.values(row).some((value) => value !== ''));

            const token = form.querySelector('input[name="_token"]').value;
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ rows }),
            });
            const result = await response.json();

            if (!response.ok) {
                const messages = Object.values(result.errors ?? {}).flat();
                throw new Error(messages.join(' ') || result.message || 'Import failed. Check the spreadsheet columns and values.');
            }

            status.textContent = result.message;
            window.setTimeout(() => window.location.reload(), 900);
        } catch (error) {
            status.textContent = error.message || 'Could not read this spreadsheet.';
            submit.disabled = false;
        }
    });
}
