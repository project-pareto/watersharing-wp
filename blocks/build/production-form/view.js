(()=>{var a;(a=jQuery)('select[name="well_pad"]').on("change",(function(){a(this).val();var e=a(this).find(":selected").data("lat"),l=a(this).find(":selected").data("long"),t=a(this).find(":selected").data("title");a("input#well_name").val(t),a("input#latitude").val(e),a("input#longitude").val(l),a("input#well_name").addClass("readonly"),a("input#latitude").addClass("readonly"),a("input#longitude").addClass("readonly")}))})();