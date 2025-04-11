$(document).ready(function(){
    try {
        $('.select2').select2({scrollAfterSelect: false, dropdownParent: $('.page-body')});
    } catch (error) {
        console.log(error);
    }
    try {
        $('.select2Tag').select2({tags: true});
    } catch (error) {
        console.log(error);
    }
    try {
        $('.dropify').dropify();
    } catch (error) {
        console.log(error);
    }
});