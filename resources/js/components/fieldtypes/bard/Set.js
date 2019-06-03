import { Node } from 'tiptap';
import Set from './Set.vue';

export default class SetNode extends Node {

    get name() {
        return 'set';
    }

    get view() {
        return Set;
    }

    get schema() {
        return {
            attrs: {
                id: {},
                enabled: { default: true },
                values: {},
            },
            group: 'block',
            selectable: false,
            draggable: true,
            parseDOM: [{
                tag: 'bard-set',
                getAttrs: dom => JSON.parse(dom.innerHTML)
            }],
            toDOM: node => ['bard-set', {}, JSON.stringify(node.attrs)]
        }
    }

    commands({ type, schema }) {
        return attrs => (state, dispatch) => {
            const { selection } = state;
            const position = selection.$cursor ? selection.$cursor.pos : selection.$to.pos;
            const node = type.create(attrs);
            const transaction = state.tr.insert(position, node);
            dispatch(transaction);
        };
    }

    stopEvent(event) {
        const draggable = !!this.schema.draggable
        if (draggable && (event instanceof DragEvent)) {
            return false
        }

        // Any other events (including pastes) should be stopped
        // which will prevent Prosemirror from handling them.
        return true
    }

}
