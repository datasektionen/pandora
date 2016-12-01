import 'whatwg-fetch'

import React from 'react'
import ReactDom from 'react-dom'

import Calendar from './calendar.jsx'

for (var i = 0; i < window.pandoraConfig.length; i++) {
	ReactDom.render(<Calendar entity={window.pandoraConfig[i].entityId} />, document.getElementById(window.pandoraConfig[i].containerId));
}

export default class App extends React.Component {
  render() {
    return (
      <Calendar />
    )
  }
}
