
//init Naja

document.addEventListener('DOMContentLoaded', naja.initialize.bind(naja));

naja.redirectHandler.addEventListener('redirect', (event) => event.detail.setHardRedirect(true))
