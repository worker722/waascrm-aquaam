import React from 'react';
import Select from 'react-select'

const FloatingSelect = (props) => {
  let zIndex = props.zIndex ?? 3;
  return (
    <>
      <div className='form-floating mt-4 select-floating ms-1' style={{zIndex : zIndex }}>
        <label>{...props.label.label ?? ''}</label>
        {props.readOnly 
        ?
        <div className='pt-1'>{props.input.defaultValue?.label ?? ''}</div>
        :
        <>
          <Select 
            {...props.label}
            className={`mt-4 ${ props.label.className }`} 
            classNamePrefix="react-select"
            {...props.input}/>
          {props.errors && <span className="text-danger m-5">{props.errors}</span>}
          </>
        }
      </div>
    </>
  );
};

export default FloatingSelect;