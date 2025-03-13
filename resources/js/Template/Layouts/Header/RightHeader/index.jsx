import React, { Fragment, useState } from 'react';
import UserHeader from './UserHeader';
import { UL } from '../../../AbstractElements';
import { Col } from 'reactstrap';
import HorecaCalc from '@/Template/Components/HorecaCalc';
import SvgIcon from '@/Template/Components/Common/Component/SvgIcon';
import { useSelector } from 'react-redux'

const RightHeader = () => {
  const actualUser = useSelector((state) => state.auth.value);
  const [modalHoreca, setModalHoreca] = useState(false);
  const toggleModalHoreca = () => setModalHoreca(!modalHoreca);

  return (
    <Fragment>
      <Col xxl='7' xl='6' md='7' className='nav-right pull-right right-header col-8 p-0 ms-auto'>
        {/* <Col md="8"> */}
        <UL attrUL={{ className: 'simple-list nav-menus flex-row' }}>
          {/*}
          <Searchbar />
          <Notificationbar />
          */}
          {actualUser && actualUser.is_tenant &&
          <li className='profile-nav white'>
            <SvgIcon iconId='calculator' style={{ stroke : 'none', width : '25px', height : '25px' }} tooltip="Calculadora HORECA" onClick={toggleModalHoreca} />
          </li>
          }
          <UserHeader />
        </UL>
        {/* </Col> */}
      </Col>

      {actualUser && actualUser.is_tenant &&
      <HorecaCalc modal={modalHoreca} onClose={toggleModalHoreca} />
      }

    </Fragment>
  );
};

export default RightHeader;
