import { useMemo } from 'react';
import { useForm } from '@inertiajs/react';
import ModuleFormShell from '../../../../Components/modules/ModuleFormShell';
import FormulaPanel from '../../../../Components/modules/FormulaPanel';
import OutputPreview from '../../../../Components/modules/OutputPreview';
import { toNumber } from '../../../../lib/format';
import { DATA_COLLECTION_TYPES, collectionMethodsFor } from '../../../../lib/dataCollectionTypes';

/** Mirrors EcosystemServiceRecord's saving hook so the preview matches the row that will be stored. */
function computeOutputs(schema, data) {
    const quantity = toNumber(data.quantity_value);
    const price = toNumber(data.unit_price);
    const area = toNumber(data.area_ha);
    const conversion = schema.key === 'WATER' && /m³|m3/.test(String(data.quantity_unit || '')) ? 1000 : 1;
    const valuePerHa = quantity * price * conversion;

    return {
        quantity,
        unit_price: price,
        value_per_ha: valuePerHa,
        total_value: valuePerHa * area,
        quantity_area: quantity * area,
    };
}

export default function Form({ project, schema, record, serviceCategories }) {
    const isEdit = Boolean(record);

    const initial = useMemo(() => {
        const base = {
            record_code: record?.record_code || '',
            service_category_label: serviceCategories[schema.service_category] || schema.service_category,
            data_collection_type: record?.data_collection_type || '',
            collection_method: record?.collection_method || '',
            notes: record?.notes || '',
        };
        schema.fields.forEach((f) => {
            base[f.name] = (record ? (record[f.name] ?? record.extra?.[f.name]) : '') ?? '';
        });
        return base;
    }, [schema, record, serviceCategories]);

    const { data, setData, post, put, processing, errors } = useForm(initial);
    const outputs = computeOutputs(schema, data);

    const fields = useMemo(() => ([
        { name: 'record_code', label: 'ID Data', type: 'text', required: true, placeholder: `Contoh: ${schema.code_prefix}-0015` },
        { name: 'service_category_label', label: 'Kategori Jasa', type: 'text', readOnly: true, hint: 'Ditentukan oleh jenis modul.' },
        ...schema.fields,
        { name: 'data_collection_type', label: 'Jenis Data', type: 'select', options: DATA_COLLECTION_TYPES.map((t) => [t.value, t.label]), placeholder: '-- Pilih jenis data --' },
        { name: 'collection_method', label: 'Metode Pengumpulan', type: 'select', options: collectionMethodsFor(data.data_collection_type).map((m) => [m.value, m.label]), placeholder: '-- Pilih metode --' },
        { name: 'notes', label: 'Catatan (opsional)', type: 'textarea', full: true, maxLength: 500, placeholder: 'Catatan tambahan (opsional)' },
    ]), [schema, data.data_collection_type]);

    const indexUrl = route('admin.modules.ecosystem.index', [project.id, schema.key]);

    function submit(e) {
        e.preventDefault();
        if (isEdit) {
            put(route('admin.modules.ecosystem.update', [project.id, schema.key, record.id]));
        } else {
            post(route('admin.modules.ecosystem.store', [project.id, schema.key]));
        }
    }

    return (
        <ModuleFormShell
            title={`${isEdit ? 'Edit' : 'Tambah'} Data ${schema.name}`}
            projectName={project.name}
            backHref={indexUrl}
            backLabel={`Kembali ke Data ${schema.name}`}
            formTitle={`Form Input ${schema.name} — ${project.name}`}
            fields={fields}
            data={data}
            setData={setData}
            errors={errors}
            processing={processing}
            onSubmit={submit}
            sidebar={
                <>
                    <FormulaPanel
                        title={`Formula ${schema.name}`}
                        formulas={schema.formula}
                        legend={schema.legend}
                        note={schema.formula_note}
                    />
                    <OutputPreview
                        rows={schema.outputs.map((out) => ({
                            label: out.label,
                            sublabel: out.sublabel,
                            value: outputs[out.source] ?? 0,
                            format: out.format,
                            unit: out.unit,
                            color: out.color,
                        }))}
                        note="Nilai dihitung langsung dari input di sebelah kiri dan disimpan bersama data."
                    />
                </>
            }
        />
    );
}
