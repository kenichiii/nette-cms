
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

      if (payload === undefined || payload === null) {
         return;
      }
      if (payload.showModal) {
         $(payload.showModal).modal();
      }
      if (payload.closeModal) {
         $(payload.closeModal).modal('hide');
      }
      $('.datagrid').find('.datagridWrapper').show();
      $('.datagrid').find('.spinner').hide();
   }
}
naja.registerExtension(new FormExtension());