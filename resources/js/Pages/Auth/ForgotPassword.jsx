import GuestLayout from '@/Template/Layouts/GuestLayout';
import InputError from '@/Template/Components/InputError';
import PrimaryButton from '@/Template/Components/PrimaryButton';
import { Head, useForm } from '@inertiajs/react';
import { Form, FormGroup, Input, Label } from 'reactstrap';
import wass from '../../../assets/images/logo/waas.png';

export default function ForgotPassword({ status, logo }) {
    const { data, setData, post, processing, errors } = useForm({
        email: '',
    });

    const submit = (e) => {
        e.preventDefault();

        post(route('password.email'));
    };

    return (
        <GuestLayout logo={logo == '' ? wass : logo}>
            <Head title="Forgot Password" />

            <div className="mb-4 text-sm text-gray-600">Ingrese su email para recuperar su clave.</div>

            {status && <div className="mb-4 font-medium text-sm text-green-600">{status}</div>}

            <form onSubmit={submit}>
                <Input 
                    className='form-control' 
                    type='email' 
                    required 
                    onChange={(e) => setData('email', e.target.value)} 
                    name="email" 
                    value={data.email} 
                />

                <InputError message={errors.email} className="mt-2" />

                <div className="flex items-center justify-end mt-4">
                    <PrimaryButton className="ms-4" disabled={processing}>
                        Enviar link de recuperación
                    </PrimaryButton>
                </div>
            </form>
        </GuestLayout>
    );
}
