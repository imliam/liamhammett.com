import setScrollbarWidth from 'set-scrollbar-width';
import './bootstrap';
import './podcast-player';
import Alpine from 'alpinejs'
import Clipboard from "@ryangjchandler/alpine-clipboard"
import goo from './goo'
import dragScroll from './drag-scroll'

setScrollbarWidth();

Alpine.plugin(Clipboard)
window.Alpine = Alpine
Alpine.start()

goo()
dragScroll()
