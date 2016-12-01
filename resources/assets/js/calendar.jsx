import React from 'react'

import getEvents from './pandora.jsx'

const pandora_url = 'https://bokning.datasektionen.se'

export default class Calendar extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            tracks: null,
            startDate: null,
            endDate: null,
            entity: props.entity,
            week: 0,
            year: 0,
            hash: location.hash
        };

        window.onhashchange = e => this.setState({hash: location.hash})
        this.today = this.today.bind(this);
        this.next = this.next.bind(this);
        this.prev = this.prev.bind(this);
    }

    componentDidMount() {
        this.today()
    }

    today(e) {
        if (e !== undefined)
            e.preventDefault()
        getEvents(this.state.entity).then(json => (
            this.setState({tracks: json.tracks, startDate: json.startDate, endDate: json.endDate, week: json.week, year: json.year})
            )
        )
    }

    next(e) {
        e.preventDefault()
        getEvents(this.state.entity, this.state.year, 1 + parseInt(this.state.week)).then(json => (
            this.setState({tracks: json.tracks, startDate: json.startDate, endDate: json.endDate, week: json.week, year: json.year})
            )
        )
    }

    prev(e) {
        e.preventDefault()
        getEvents(this.state.entity, this.state.year, this.state.week - 1).then(json => (
            this.setState({tracks: json.tracks, startDate: json.startDate, endDate: json.endDate, week: json.week, year: json.year})
            )
        )
    }

    /**
     * Parses a date from string.
     * 
     * @param  string str the string to interpret
     * @return Date       the date object
     */
     parseDate(str) {
        var match = str.match(/^(\d+)-(\d+)-(\d+).*$/);
        return new Date(match[1], match[2] - 1, match[3], 0, 0, 0);
    }

    /**
     * Parses a date from string.
     * 
     * @param  string str the string to interpret
     * @return Date       the date object
     */
     parseDateTime(str) {
        var match = str.match(/^(\d+)-(\d+)-(\d+) (\d+):(\d+):(\d+)$/);
        return new Date(match[1], match[2] - 1, match[3], match[4], match[5], match[6]);
    }

    /**
     * Get all dates between the two input dates
     * @param  string fromDate Y-m-d
     * @param  string toDate   Y-m-d
     * @return [{yyyymmdd, jn}]
     */
     getDates(fromDate, toDate) {
        // Parse the input dates
        var currentDate = this.parseDate(fromDate);
        var endDate     = this.parseDate(toDate);

        // Calculate iteration variables
        var getTimeDiff = Math.abs(currentDate.getTime() - endDate.getTime());
        var dateRange   = Math.floor(getTimeDiff / (1000 * 3600 * 24)) - 1;

        // The array to store dates
        var dates = new Array();

        var day, month;
        // Iterate over the dates and add them to the list
        for (var i = 0; i <= dateRange; i++) {
            day = (currentDate.getDate() < 10 ? '0' : '') + currentDate.getDate();
            month = (currentDate.getMonth() < 9 ? '0' : '') + (currentDate.getMonth() + 1);
            dates.push({
                yyyymmdd: currentDate.getFullYear() + "-" + month + "-" + day, 
                jn: currentDate.getDate() + "/" + (currentDate.getMonth() + 1)
            });
            currentDate.setDate(currentDate.getDate() + 1);
        }
        return dates;
    }

    /**
     * Returns a css style for event box (positioning)
     */
     eventStyle(startDate, event, track) {
        var start    = this.parseDateTime(event.start).getTime() / 1000;
        var end      = this.parseDateTime(event.end).getTime() / 1000;
        var morning  = this.parseDate(event.start).getTime() / 1000;
        var startDay = this.parseDate(startDate).getTime() / 1000;

        return {
            top:       (start - morning) / 3600 * 25,
            height:    (end - start) / 3600 * 25,
            minHeight: (end - start) / 3600 * 25,
            maxHeight: (end - start) / 3600 * 25,
            left:      (5 + Math.floor((start - startDay) / 3600 / 24) * 13.57 + track * 13.57 / event.numtracks) + '%',
            width:     (13.57 / event.numtracks * event.colspan) + '%'
        }
    };

    /**
     * Renders the component
     */
     render() {
        if (this.state.tracks === null) {
            return <div>Laddar...</div>
        }

        var curDate = this.parseDate(this.state.startDate);
        var week = curDate.getWeekNumber();
        var year = curDate.getFullYear();
        var dateInterval = this.getDates(this.state.startDate, this.state.endDate);

        return (
            <div className="outer">
                <div className="clear"></div>
                    <div className="center calendar">
                        <div className="controls">
                            <a href="#" onClick={this.prev} className="prev theme-color">&lt;</a>
                            <span>Vecka {week} år {year}</span>
                            <a href="#" onClick={this.next} className="next theme-color">&gt;</a>
                            <a href="#" onClick={this.today} className="today theme-color">Idag</a>
                            <a href={pandora_url + '/bookings/' + this.state.entity + '/book'} className="today theme-color">Boka</a>
                        </div>
                    </div>
                    <div className="clear"></div>
                    <div className="week-component">
                        <div className="week">
                            <div className="title-row">
                                <div className="hour placeholder"></div>
                                {dateInterval.map(date => 
                                    <div key={date.yyyymmdd} className="day">
                                        <div className="title">{date.jn}</div>
                                    </div>
                                )}
                            </div>
                            <div className="hour-row" id="scroll-bottom">
                                <div className="legend">
                                    {[...Array(24)].map((_, i) => i++).map(x => 
                                        <div key={x} className="hour"><span>{x}:00</span></div>
                                    )}
                                    <div className="clear"></div>
                                </div>
                                {[...Array(7)].map((_, i) => i++).map(x => 
                                    <div key={x + "d"} className="day">
                                        {[...Array(24)].map((_, i) => i++).map(y => 
                                            <div key={x + "_" + y} className="hour"></div>
                                        )}
                                        <div className="clear"></div>
                                    </div>
                                )}
                                {dateInterval.map(date => 
                                    this.state.tracks.filter(track => track.date.yyyymmdd === date.yyyymmdd).map(elem => 
                                        elem.events.map((track, index) => 
                                            track.map(event => 
                                                <div className="event" key={event.id} style={this.eventStyle(this.state.startDate, event, index)}>
                                                    <a className="content theme-color" href={pandora_url + '/events/' + event.id}>
                                                        <span className="from">{event.startHi}</span>
                                                        <span className="to">{event.endHi}</span>
                                                        <div className="text">
                                                            {event.title}
                                                        </div>
                                                    </a>
                                                </div>
                                            )
                                        )
                                    )
                                )}
                        </div>
                    </div>
                </div>
            </div>
        )
    }
}

Date.prototype.getWeekNumber = function(){
    var d = new Date(+this);
    d.setHours(0,0,0,0);
    d.setDate(d.getDate()+4-(d.getDay()||7));
    return Math.ceil((((d-new Date(d.getFullYear(),0,1))/8.64e7)+1)/7);
};