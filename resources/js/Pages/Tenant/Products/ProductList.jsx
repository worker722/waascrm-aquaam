import React, { Fragment, useState, useEffect } from "react";
import { Breadcrumbs, Btn } from "../../../Template/AbstractElements";
import AuthenticatedLayout from '@/Template/Layouts/AuthenticatedLayout';
import { Head, router } from '@inertiajs/react';
import axios from "axios";
import Edit from '@/Template/CommonElements/Edit';
import { Check, X } from 'react-feather';
import { Image } from "react-bootstrap";
import Icon from '@/Template/CommonElements/Icon';
import FilterTable from "@/Template/Components/FilterTable";
import Switch from '@/Template/CommonElements/Switch';

export default function ProductList({ auth, title, filters }) {
    const [dataList, setDataList] = useState([]);
    const [extraCatalogData, setExtraCatalogData] = useState([]);

    const getProducts = async (d) => {
        let extraData = [];
        if (d) {
            for (const [key, value] of Object.entries(d)) extraData.push(key + '=' + value);
            setExtraCatalogData(extraData);
        }

        const response = await axios.post(route('prs.list'), d);
        setDataList(response.data);
    }

    const enableDisable = async (ids, checked = null) => {
        await axios.post(route('prs.change.status'), { ids, checked });
        getProducts();
    }

    useEffect(() => {
        getProducts();
    }, []);

    const tableColumns = [
        {
            name: '',
            selector: row => {
                return (
                    <Image height={50} src={row['main_image']} rounded />
                )
            },
            sortable: true,
            center: false,
            maxWidth: "100px"
        },
        {
            name: 'Referencia',
            selector: row => {
                return (
                    <>
                        {row['inner_active'] ? <Check color="green" size={15} /> : <X color="red" size={15} />}
                        <span className="ms-1" style={{ position: 'relative', top: '-4px' }}>{row['final_model']}</span>
                    </>
                )
            },
            sortable: true,
            center: false,
            maxWidth: "140px"
        },
        {
            name: 'Nombre',
            selector: row => row['final_name'],
            sortable: true,
            center: false,
            maxWidth: "300px"
        },
        {
            name: 'Familia',
            selector: row => row['family_name'],
            sortable: true,
            center: false,
            maxWidth: "150px"
        },
        {
            name: 'Descripción',
            selector: row => row['description'],
            sortable: true,
            center: false,
        },
        {
            name: "Visibilidad",
            selector: row => (
                <Switch
                    nomargin
                    input={{ onChange: () => enableDisable([row['id']]), name: 'visibility', defaultChecked: row['inner_active'] }}
                />
            ),
            sortable: false,
            center: false,
            maxWidth: "140px"
        },
        {
            name: 'Acciones',
            selector: (row) => {
                return (
                    <>
                        <a href={route('prs.pdf', row['id'])} target="_blank">
                            <Icon icon="File" id={'ficha' + row['id']} tooltip="Ficha Técnica" />
                        </a>
                        &nbsp;
                        &nbsp;
                        <Edit onClick={() => router.visit(route('prs.edit', row['id']))} id={'edit-' + row['id']} />
                    </>
                )
            },
            sortable: false,
            center: true,
            maxWidth: "100px"
        },
    ];
    const CustomActionsMemo = React.memo(({ data }) => {
        const ids = data.map(item => item.id);
        const disVisible = !data.find(item => !item.inner_active);
        const disInVisible = !data.find(item => item.inner_active);
        return (
            <div>
                <Btn attrBtn={{ color: 'info', className: 'btn-sm', disabled: disVisible, onClick: () => enableDisable(ids, true) }}>
                    Hacer todo visible
                </Btn>
                &nbsp;
                &nbsp;
                <Btn attrBtn={{ color: 'danger', className: 'btn-sm', disabled: disInVisible, onClick: () => enableDisable(ids, false) }}>
                    Hacer todo invisible
                </Btn>
            </div>
        )
    })

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title={title} />
            <Fragment>
                <Breadcrumbs mainTitle={title} title={title} />

                <div className="d-flex flex-row-reverse mb-2">
                    <a href={route('prs.pdf', 0) + '?' + extraCatalogData.join('&')} target="_blank" className="me-1">
                        <Btn attrBtn={{ color: 'primary', className: 'btn-sm' }}>Descargar Catalogo</Btn>
                    </a>
                </div>

                <FilterTable
                    CustomActions={CustomActionsMemo}
                    dataList={dataList}
                    tableColumns={tableColumns}
                    filters={filters}
                    getList={(d) => getProducts(d)}
                />
            </Fragment>
        </AuthenticatedLayout>
    )
}