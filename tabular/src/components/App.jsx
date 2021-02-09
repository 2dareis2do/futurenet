'use strict';

import React from 'react';
import Layout from './Layout';
import Offers from './Offers';

const offersAPI = ['//search-api.fie.future.net.uk/widget.php?id=review&site=TRD&model_name=iPad_Air'];

export default class App extends React.Component {

  render() {
    return(
      <Layout>
        <Offers offersAPI={offersAPI} />
      </Layout>
    );
  }
}