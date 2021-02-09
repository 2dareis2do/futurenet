'use strict';

import axios from 'axios';
import React from 'react';
import { Link } from 'react-router';

const tableStyle = {
  width: '100%',
  tableLayout: 'fixed',
  display: 'table',
  paddingBottom: '1rem'
};

const tHeadStyle = {
  display: "table-header-group"
}

const thStyle = {
  fontWeight: '600',
  textAlign: 'left',
  lineHeight: '2',
  display: "table-cell"
}

const trStyle = {
  display: "table-row"
}

const tdStyle = {
  display: "table-cell"
}

const tBodyStyle = {
  display: "table-row-group"
}

const containerStyle = {
  padding: '1rem'
}

const titleStyle = {
  color: 'blue',
  WebkitTextFillColor: 'initial',
  fontSize: '1.8rem',
  paddingBottom: '1rem'
}

const buttonStyle = {
  cursor: 'pointer'
}

const supStyle = {
  color: 'red',
  WebkitTextFillColor: 'initial'
}

const blueStyle = {
  color: 'blue'
}

const redStyle = {
  color: 'red'
}

const noneStyle = {
  display: 'none'
}

export default class Offers extends React.Component {

  constructor(props) {
    super(props);
    this.state = {
      films: [],
      people: [],
      order: [],
      offers: []
    };
  }

  componentDidMount() {

    this.importState = this.importState.bind(this);

    let offersAPI = this.props.offersAPI;

    axios.all([
      axios.get(offersAPI),
    ])
      .then(this.checkStatus)
      .then(this.parseJSON)
      .then(this.importState)
      .catch(function (err) {
        console.log(err);
      });
  }

  componentWillMount() {
  }

  componentWillReceiveProps(nextProps) {
  }

  checkStatus(msgs) {
    if (msgs[0].status >= 200 && msgs[0].status < 300) {
      try {
        console.log('SUCCESS');
        return msgs;
      }
      catch (err) {
        // throw err.messag - may need looking at ?
        let error = new Error(msgs.statusText);
        error.msgs = msgs;
        throw error;
      }
    }
  }

  importState(items) {
    this.setState({
      offers: items[0].data.widget.data.offers
    });
  }

  parseJSON(response) {
    return response;
  }

  test() {
    console.log('test')
    console.log(this.state);;
  }

  sortCol(items, direction) {
    if (direction === "ASC") {
      items.sort(function (b, a) {
        if (a.offer.name < b.offer.name)
          return -1;
        if (a.offer.name > b.offer.name)
          return 1;
        return 0;
      });
    } else {
      items.sort(function (a, b) {
        if (a.offer.name < b.offer.name)
          return -1;
        if (a.offer.name > b.offer.name)
          return 1;
        return 0;
      });
    }

    this.setState({ offers: items });
  }

  handleClickSort() {

    if (this.state.order !== "ASC") {
      this.state.order = "ASC";
    } else {
      this.state.order = "DESC";
    }
    this.sortCol(this.state.offers, this.state.order);
  }

  render() {

    return (

      <div style={containerStyle}>

        <h1 style={titleStyle}>Acme Offers:</h1>

        {this.props.children}

        <table style={tableStyle}>
          <thead style={tHeadStyle}>
            <tr style={trStyle}>
              <th style={thStyle}>
                <button onClick={() => this.handleClickSort()} title="click to sort" style={buttonStyle}>Product Name
                    <sup style={Object.assign({}, supStyle, redStyle)}>*</sup>
                </button>
              </th>
              <th style={thStyle}>Price</th>
              <th style={thStyle}>Merchant Name</th>
              <th style={thStyle}>Merchant Logo</th>
              <th style={thStyle}>Link</th>
            </tr>
          </thead>
          <tbody style={tBodyStyle}>
            {this.state.offers.map((offer, index) => {
              return (
                <tr key={index} style={trStyle}>
                  <td style={tdStyle}>{offer.offer.display_name}</td>
                  <td style={tdStyle}>{offer.offer.price}</td>
                  <td style={tdStyle}>{offer.merchant.name}</td>
                  <td style={tdStyle}><img src={offer.merchant.logo_url} title={offer.title} alt={offer.title}/></td>

                  <td style={tdStyle}>
                   <Link
                    href={offer.offer.link} target="_blank">
                    Offer Link
                  </Link>
                  </td>
                </tr>
              );
            })}
          </tbody>
        </table>

        <p><sup style={Object.assign({}, supStyle, redStyle)}>*</sup> Denotes sortable column - click header to sort</p>

      </div>

    );
  }

}

