import React, { Fragment, useState, useEffect, useContext } from "react";
import { Breadcrumbs } from "../../../Template/AbstractElements";
import AuthenticatedLayout from '@/Template/Layouts/AuthenticatedLayout';
import { Head, router } from '@inertiajs/react';
import axios from "axios";
import Edit from '@/Template/CommonElements/Edit';
import Trash from '@/Template/CommonElements/Trash';
import AddBtn from '@/Template/CommonElements/AddBtn';
import MainDataContext from '@/Template/_helper/MainData';
import Icon from "@/Template/CommonElements/Icon";
import NotesModal from "@/Template/Components/NotesModal";
import Email from "@/Template/CommonElements/Email";
import Phone from "@/Template/CommonElements/Phone";
import TrafficLights from "@/Template/Components/TrafficLights";
import FilterTable from "@/Template/Components/FilterTable";
import Address from "@/Template/Components/Address";

export default function ClientList({ auth, title, isClient, filters, filtered}) {
    const [dataList, setDataList] = useState([]);
    const { handleDelete, deleteCounter } = useContext(MainDataContext);
    const [tooltip, setTooltip] = useState(false);
    const toggle = () => setTooltip(!tooltip);

    const [clientId, setClientId] = useState(0);
    const [notesModal, setNotesModal] = useState(false);
    const toggleNotesModal = () => setNotesModal(!notesModal);

    const getClients = async (d) => {
        const response = await axios.post(route(isClient ? 'clients.list' : 'contacts.list'), d);
        setDataList(response.data);
    }

    useEffect(() => {
        getClients(filtered);
    }, [deleteCounter]);

    const tableColumns = [
        {
            name: 'Referencia',
            selector: row => row['external_id'],
            sortable: true,
            center: false
        },
        {
            name: 'Nombre',
            selector: (row) => {
                return (
                    <div className={row['expired'] == 1 ? 'text-warning' : (row['expired'] == 2 ? 'text-danger' : '')}>
                        <div>{row['company_name']}</div>
                        <div><small>{row['business_name']}</small></div>
                    </div>
                )
            },
            sortable: true,
            center: false,
        },
        {
            name: 'Actividad',
            selector: (row) => row['activity']?.name,
            sortable: true,
            center: false,
        },
        {
            name: 'Dirección',
            selector: (row) => <Address address={row['address_complete'] ?? {}} />,
            sortable: true,
            center: false
        },
        {
            name: 'Email / Teléfono',
            selector: row => {
                return (
                    <>
                        {row['email'] && <div><Email email={row['email']} /></div>}
                        {row['phone'] && <div><Phone phone={row['phone']} /></div>}
                    </>
                )
                
            },
            sortable: true,
            center: false,
        },
        {
            name: 'Estado',
            selector: (row) => {
                return (
                    <>
                        <div><div className={`badge bg-success`}>{row['status']?.name}</div></div>
                        <div className={`badge badge-primary`}>Hace {row['last_change']}</div> 
                    </>
                )
            },
            sortable: true,
            center: false,
            maxWidth: "130px"
        },
        {
            name: 'A.',
            selector: (row) => <TrafficLights data={row['tasksLights']} url={route('tasks', {'cid' : row['id'], 'back' : isClient ? 'clients' : 'contacts'})} />,
            sortable: false,
            center: false,
            maxWidth: "100px"
        },
        {
            name: 'Pr.',
            selector: (row) => <TrafficLights data={row['budgetsLigths']} url={route('budgets.index', row['id']) + '?'}/>,
            sortable: false,
            center: false,
            maxWidth: "100px"
        },
        {
            name: 'Acciones',
            selector: (row) => {
                return (
                    <>
                        <Icon icon="Eye" id={'Eye-' + row['id']} tooltip="Ver" onClick={() => router.visit(route(isClient ? 'clients.show' : 'contacts.show', row['id']))}  className="me-1"/>
                        
                        {!isClient &&
                        <Icon 
                            icon="User" 
                            id={'usr-' + row['id']} 
                            tooltip="Convertir en Cliente" 
                            onClick={() => {
                                router.post(route('contacts.convert', row['id']), {
                                    onSuccess : () => {
                                        getClients()
                                    }
                                });
                            }}
                            className="me-1"
                        />
                        }
                        <Icon icon="MessageSquare" id={'msg-' + row['id']} tooltip="Comentarios" onClick={() => {toggleNotesModal(); setClientId(row['id'])}} className="me-1"/>
                        <Icon icon="FileText" id={'ft-' + row['id']} tooltip="Propuestas" onClick={() => router.visit(route('budgets.index', row['id']))}  className="me-1"/>
                        <Edit onClick={() => router.visit(route(isClient ? 'clients.edit' : 'contacts.edit', row['id']))} id={'edit-' + row['id']}/>
                        <Trash onClick={() => handleDelete(route(isClient ? 'clients.destroy' : 'contacts.destroy', row['id']))} id={'delete-' + row['id']}/>
                    </>
                )
            },
            sortable: false,
            center: true,
            maxWidth: "150px"
        },
    ];

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title={title} />
            <Fragment>
                <Breadcrumbs mainTitle={title} title={title} />

                <FilterTable
                    dataList={dataList}
                    tableColumns={tableColumns}
                    filters={filters}
                    getList={(d) => getClients(d)}
                /> 

                <AddBtn onClick={() => router.visit(route(isClient ? 'clients.create' : 'contacts.create'))} />

                <NotesModal
                    type="1"
                    id={clientId}
                    modal={notesModal}
                    onClose={toggleNotesModal}
                />
            </Fragment>
        </AuthenticatedLayout>
    )
}