document.addEventListener("DOMContentLoaded", () => {
    var membership_model = document.getElementById('academy_pmpro_membership_model')
    if(membership_model){
        var category_wise_membership = document.getElementById('category_wise_membership');
        membership_model.addEventListener('change', function(e){
            if('category_wise_membership' === e.target.value){
                category_wise_membership.style.display = 'table-row';
            }else {
                category_wise_membership.style.display = 'none';
            }
        })
    }
});