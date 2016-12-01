const pandora_api_url = 'https://bokning.local/api'

const getJson = path => fetch(pandora_api_url + path).then(res => res.json())

const getEvents = (entityId, year, week) => getJson('/events/' + entityId + (year !== undefined ? '/' + year + (week !== undefined ? '/' + week : '') : ''))


export default getEvents

export {
  getEvents
}