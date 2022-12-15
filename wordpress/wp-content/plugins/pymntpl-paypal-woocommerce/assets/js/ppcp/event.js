import {createHooks} from "@wordpress/hooks";

class Event {

    constructor() {
        this.hooks = createHooks();
    }

    on(event, callback, priority = 10) {
        this.hooks.addAction(event, 'wcPPCP', callback, priority);
    }

    trigger(event, ...args) {
        this.hooks.doAction(event, ...args);
    }

}

export default Event;