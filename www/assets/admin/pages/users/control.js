
function init_users() {
   $('.newRecordButton').click(function() {
      $('#addNewUserNewModal').modal();

      return false;
   });

}

init_users();

class FormExtension {
   initialize(naja) {
      naja.addEventListener('complete', this.form.bind(this));
   }

   form(event) {
      qmandatagrid();
      init_users();
      let payload = event.detail.payload;

      if (payload === undefined || payload === null || !payload.hasOwnProperty('afterForm')) {
         return;
      }
      if (payload.openModal) {
         $(payload.openModal).modal();
      }
      if (payload.closeModal) {
         $(payload.closeModal).modal('hide');
      }
   }
}
naja.registerExtension(new FormExtension());