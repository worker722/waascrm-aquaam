import React, { Fragment, useState, useEffect } from 'react';
import { PlusSquare } from 'react-feather';
import { toast } from 'react-toastify';
import { H4, H6, LI, P, UL, Image } from './../../AbstractElements';
import { CardBody, CardHeader, Input, Media } from 'reactstrap';
import { useForm, router } from '@inertiajs/react';


const FileManager = (props) => {
    const [searchTerm, setSearchTerm] = useState('');
    const [myfile, setMyFile] = useState(props.files);
    const [isReadOnly, setIsReadOnly] = useState(props.readOnly || false);

    useEffect(() => {
        
    }, []);

    const handleChange = (event) => {
        event.preventDefault();
        setSearchTerm(event.target.value);
    };

    const deleteFile = (k) => {
        let myfiles = myfile.filter((data, i) => i !== k);
        setMyFile(myfiles);
        props.setFiles(myfiles);
    }

    const changeInput = (e, i) => {
        let myfiles = [...myfile];
        myfiles[i].title = e.target.value;
        setMyFile(myfiles);
        props.setFiles(myfiles);
    }

    const changeSelect = (e, i) => {
        let myfiles = [...myfile];
        myfiles[i].image_type = e.target.value;
        setMyFile(myfiles);
        props.setFiles(myfiles);
    }

    const filelist = myfile.filter((data) => {
        if (searchTerm == null) {
            return data;
        } else if (data.file.toLowerCase().includes(searchTerm.toLowerCase())) {
            return data;
        }
    }).map((data, i) => {
        let icon = data.type == 'image' ? 'fa fa-file-image-o' : 'fa fa-file-o';
        let imageType = data.image_type;
        return (
            <LI attrLI={{ className: 'file-box' }} key={i}>
                <a href={data.img} target='_blank'>
                    <div className='file-top' style={{backgroundImage : "url('"+data.img+"')", backgroundSize : "cover" }}>
                        {data.type != 'image' ? 
                        <i className={icon}></i> : 
                        null
                        }
                    </div>
                </a>
                {!isReadOnly && <i className='fa fa-times f-18 remove pointer text-danger' onClick={() => deleteFile(i)}></i> }
                <div className='file-bottom'>
                    <H6>{data.file}</H6>
                    <P attrPara={{ className: 'mb-0 mb-1' }}>{formatBytes(data.size)}</P>
                    {!isReadOnly &&
                    <P>
                        <Input placeholder='Titulo' value={data.title} onChange={(e) => changeInput(e, i)}/>
                    </P>
                    }
                    {
                    props.id == 'images' ?
                    <Input name="select" type="select" value={data.image_type} onChange={(e) => changeSelect(e, i)}>
                        <option value='0'>Varios</option>
                        <option value='1'>Principal</option>
                        <option value='2'>Ficha Técnica</option>
                        <option value='3'>Despiece</option>
                        <option value='4'>Vista Lateral</option>
                    </Input> : null
                    } 
                </div>
            </LI>
        );
        });

    const getFile = () => {
        document.getElementById('upfile' + props.id).click();
    };

    const onFileChange = async (e) => {
        const files = e.target.files;
        router.post(props.uploadUrl, {files : files},
            {
                onSuccess: (y) => {
                    console.log(y);
                    let files = y.props.flash.files;
                    if (files && files.length > 0){
                        let myfiles = [...myfile];
                        for (let i = 0; i < files.length; i++) {
                            myfiles.push({
                                id: 0,
                                title: files[i].name,
                                file : files[i].name,
                                size: `${files[i].size}`,
                                img : files[i].url,
                                type : files[i].type,
                                order : myfiles.length + 1 + i
                            });
                        }
                        setMyFile(myfiles);
                        props.setFiles(myfiles);
                        toast.success('Archivo subido correctamente!');
                    }
                },
                onError: (errors) => {
                    console.log(errors);
                }
            }
        )
    };

    function formatBytes(bytes, decimals = 2) {
        if (!+bytes) return '0 Bytes'
    
        const k = 1024
        const dm = decimals < 0 ? 0 : decimals
        const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB']
    
        const i = Math.floor(Math.log(bytes) / Math.log(k))
    
        return `${parseFloat((bytes / Math.pow(k, i)).toFixed(dm))} ${sizes[i]}`
    }

    return (
        <Fragment >
            <div className='file-content'>
                <CardHeader style={{ paddingTop : '0px', paddingBottom : '10px' }}>
                    <Media>
                        {props.search != 'hide' ?  
                        <div className='search-file form-inline'>
                            <div className='mb-0 form-group'>
                                <i className='fa fa-search'></i>
                                <input className='form-control-plaintext' type='text' value={searchTerm} onChange={(e) => handleChange(e)} placeholder='Buscar...' />
                            </div>
                        </div>
                        :
                        <H4 attrH4={{ className: 'mb-3' }}>{ props.title }</H4>
                        }
                        <Media body className='text-end'>
                            {!isReadOnly &&
                            <div className='d-inline-flex'>
                                <div className='btn btn-primary' onClick={getFile}>
                                    <PlusSquare />
                                    Agregar
                                </div>
                                <div style={{ height: '0px', width: '0px', overflow: 'hidden' }}>
                                    <Input accept={props.accept} id={'upfile' + props.id} multiple type='file' onChange={(e) => onFileChange(e)} />
                                </div>
                            </div>
                            }
                        </Media>
                    </Media>
                </CardHeader>
                {filelist.length ? (
                    <CardBody className='file-manager'>
                        {props.search != 'hide' && <H4 attrH4={{ className: 'mb-3' }}>{ props.title }</H4> }
                        <UL attrUL={{ className: 'simple-list files' }}> 
                            {filelist}
                        </UL>
                    </CardBody>
                ) : (
                    <H4 attrH4={{ className: 'm-3 text-center' }}>No hay archivos</H4>
                )}
            </div>
        </Fragment>
      );
};

export default FileManager;