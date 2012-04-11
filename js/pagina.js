var Pagina = Backbone.Model.extend({
    
});

var PaginaView = Backbone.View.extend({
    el: $("#contenido"),
    $el: null,
    template: $("#paginaTmpl").template(),
    initialize: function() {
        this.model.view = this;
    },
    render: function() {
        this.$el = $(this.el);
        this.el.empty();
        $flechas.fadeOut();
        this.$el.html($.tmpl(this.template, this.model));
        this.$el.find("#inner").hide().fadeIn();
        return this;
    },
    remove: function() {
//        console.log("SubastaMiniView.remove");
        $(this.el).fadeTo(1, 0.5);
    }
});

