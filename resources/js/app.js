import setScrollbarWidth from 'set-scrollbar-width';
import './bootstrap';
import Alpine from 'alpinejs'
import Clipboard from "@ryangjchandler/alpine-clipboard"

setScrollbarWidth();

Alpine.plugin(Clipboard)
window.Alpine = Alpine
Alpine.start()
