import React, { Fragment, useEffect } from "react";
import { Breadcrumbs, Btn } from "./../../../Template/AbstractElements";
import AuthenticatedLayout from '@/Template/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';
import FloatingInput from '@/Template/CommonElements/FloatingInput';
import { Form, Card, CardBody, CardFooter, Row, Col, CardHeader } from 'reactstrap';
import { Plus, Trash2 } from 'react-feather';
import { toast } from "react-toastify";


export default function BrandsForm({ auth, title, brands }) {
    const { data, setData, post, processing, errors, reset, clearErrors } = useForm({
        watermarks: [], // [{id:1, brand:'', type:0, prices:[{num:1, price:1.5}]}]
        aquaservice: [], // [{id:1, brand:'', type:1, prices:[{num:1, price:1.5, home:'home | company'}]}]
    });

    useEffect(() => {
        let waters = brands.filter(item => item.type == 0);
        let aquaservice = brands.filter(item => item.type == 1);
        setData(data => ({ ...data, ['watermarks']: waters }));
        setData(data => ({ ...data, ['aquaservice']: aquaservice }));
    }, [brands]);

    const handAddWaters = () => {
        let waters = data.watermarks;
        waters.push({
            id: 0, brand: '', type: 0, prices: [{ num: 1, price: 0 }]
        });
        setData(data => ({ ...data, ['watermarks']: waters }))
    }
    const handleAddService = () => {
        let aquaservice = data.aquaservice;
        aquaservice.push({
            id: 0,
            brand: '',
            type: 1,
            prices: [
                ...(new Array(7).fill(1).map((_, idx) => ({ num: idx + 2, price: 0, home: 'home' }))),
                ...(new Array(7).fill(1).map((_, idx) => ({ num: idx + 2, price: 0, home: 'company' }))),
            ]
        });
        setData(data => ({ ...data, ['aquaservice']: aquaservice }))
    }

    const handleDeleteWaters = (i) => {
        let waters = data.watermarks;
        waters.splice(i, 1);
        setData(data => ({ ...data, ['watermarks']: waters }))
    }
    const handleDeleteService = (i) => {
        let aquaservice = data.aquaservice;
        aquaservice.splice(i, 1);
        setData(data => ({ ...data, ['aquaservice']: aquaservice }))
    }

    const handleChangeWaters = (i, e) => {
        let waters = data.watermarks;
        let key = e.target.name;
        let value = e.target.value;

        if (key == 'brand') {
            waters[i].brand = value;
            setData(data => ({ ...data, ['watermarks']: waters }))
        } else {
            if (waters[i].prices.length > 0) waters[i].prices[0].price = value;
            else waters[i].prices = [{ price: value, type: 0, num: key.split('_')[1] }];

            setData(data => ({ ...data, ['watermarks']: waters }))
        }
    }

    const handleChangeService = (i, home, e) => { //home=> home|company
        let aquaservice = data.aquaservice;
        let key = e.target.name;
        let value = e.target.value;

        if (key == 'brand') {
            aquaservice[i].brand = value;
            setData(data => ({ ...data, ['aquaservice']: aquaservice }))
        } else {
            if (!aquaservice[i].prices) aquaservice[i].prices = [];

            let priceIdx = aquaservice[i].prices.findIndex(item => `price_${item.num}` == key && item.home == home);
            if (priceIdx >= 0) {
                aquaservice[i].prices[priceIdx].price = value;
                setData(data => ({ ...data, ['aquaservice']: aquaservice }))
            } else {
                aquaservice[i].prices.push({ price: value, type: 1, num: key.split('_')[1], home });
                setData(data => ({ ...data, ['aquaservice']: aquaservice }))
            }
        }
    }


    const saveForm = async () => {
        let waters = data.watermarks;
        let aquaservice = data.aquaservice;
        const isWaterUncompleted = waters.filter(item => !item.brand || item.prices.filter(tmp => !(tmp.price > 0)).length > 0).length > 0;
        const isAquaUncompleted = aquaservice.filter(item => !item.brand || item.prices.filter(tmp => !(tmp.price > 0)).length > 0).length > 0;
        if (isWaterUncompleted || isAquaUncompleted) {
            return toast.error('Por favor completa todos los campos');
        }
        post(route('brands.store'));
    };
    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title={title} />
            <Fragment>
                <Form className='theme-form'>
                    <Breadcrumbs mainTitle={title} title={title} />
                    <Card>
                        <CardHeader className="d-flex align-items-center gap-2">
                            <h5 className="mb-0">Marcas de agua</h5>
                            <div
                                className="d-flex justify-content-center align-items-center rounded-circle bg-primary text-white"
                                style={{ width: 32, height: 32, cursor: 'pointer' }}
                                onClick={handAddWaters}
                            >
                                <Plus size={16} />
                            </div>
                        </CardHeader>
                        <CardBody>
                            <Row>
                                {data.watermarks.map((item, i) => (
                                    <Col xs='12' md='3' className="mb-5 position-relative" key={i}>
                                        {data.watermarks.length > 1 &&
                                            <div
                                                className="position-absolute"
                                                style={{ top: 10, right: 20, cursor: 'pointer', zIndex: 10 }}
                                                onClick={() => handleDeleteWaters(i)}
                                            >
                                                <Trash2 size={20} className="text-danger" />
                                            </div>
                                        }
                                        <FloatingInput
                                            label={{ label: 'Marca' }}
                                            input={{ placeholder: 'Marca', value: item.brand, onChange: (e) => handleChangeWaters(i, e), name: 'brand' }}
                                        />
                                        <FloatingInput
                                            label={{ label: 'Precio' }}
                                            input={{ placeholder: 'Precio', value: item.prices.length > 0 ? item.prices[0].price || 0 : 0, onChange: (e) => handleChangeWaters(i, e), name: 'price', type: 'number' }}
                                        />
                                    </Col>
                                ))}
                            </Row>
                        </CardBody>
                    </Card>
                    <Card>
                        <CardHeader className="d-flex align-items-center gap-2">
                            <h5 className="mb-0">Precios Aquaservice</h5>
                            <div
                                className="d-flex justify-content-center align-items-center rounded-circle bg-primary text-white"
                                style={{ width: 32, height: 32, cursor: 'pointer' }}
                                onClick={handleAddService}
                            >
                                <Plus size={16} />
                            </div>
                        </CardHeader>
                        <CardBody>
                            <Row>
                                {data.aquaservice.map((item, i) => (
                                    <Col xs='12' md='12' className="mb-5 position-relative" key={i}>
                                        {data.aquaservice.length > 1 &&
                                            <div
                                                className="position-absolute"
                                                style={{ top: 0, right: 20, cursor: 'pointer', zIndex: 10 }}
                                                onClick={() => handleDeleteService(i)}
                                            >
                                                <Trash2 size={24} className="text-danger" />
                                            </div>
                                        }
                                        <FloatingInput
                                            label={{ label: 'Marca' }}
                                            input={{ placeholder: 'Marca', value: item.brand, onChange: (e) => handleChangeService(i, '', e), name: 'brand' }}
                                        />
                                        {new Array(2).fill(1).map((_, type_idx) => (
                                            <Row>
                                                {new Array(7).fill(1)
                                                    .map((_, num_idx) => {
                                                        const priceIdx = item.prices.findIndex(item => item.num == num_idx + 2 && item.home == (type_idx == 0 ? 'home' : 'company'));
                                                        const price = priceIdx >= 0 ? item.prices[priceIdx].price : 0;
                                                        return (
                                                            <Col xs='12' md='2' key={'k-' + i + '-' + type_idx + '-' + num_idx}>
                                                                <FloatingInput
                                                                    label={{ label: `Precio ${type_idx == 0 ? 'Hogar' : 'Empresa'} ${num_idx + 2} botellas` }}
                                                                    input={{
                                                                        placeholder: 'Precio',
                                                                        value: price,
                                                                        onChange: (e) => handleChangeService(i, type_idx == 0 ? 'home' : 'company', e),
                                                                        name: `price_${num_idx + 2}`,
                                                                        type: 'number'
                                                                    }}
                                                                />
                                                            </Col>
                                                        )
                                                    })}
                                            </Row>
                                        ))
                                        }
                                    </Col>
                                ))}
                            </Row>
                        </CardBody>
                        <CardFooter className="text-end">
                            <Btn attrBtn={{ color: 'primary save-btn', onClick: saveForm, disabled: processing }}>Guardar</Btn>
                            <Btn attrBtn={{ color: 'secondary cancel-btn ms-2', onClick: () => location.reload() }} >Volver</Btn>
                        </CardFooter>
                    </Card>
                </Form>
            </Fragment>
        </AuthenticatedLayout>
    )
}