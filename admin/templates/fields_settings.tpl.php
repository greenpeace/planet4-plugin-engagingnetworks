<script type="text/html" id="tmpl-en-fields">
    <h2 class="page-header">List of fields</h2>
    <p></p>
    <h5><%= col.length %> fields defined</h5>
    <ul class="media-list row fields-container"></ul>
    <br>
    <p class="">
        <a href="#fields/new" class="btn button button-primary">Add New Field</a>
    </p>
</script>

<script type="text/html" id="tmpl-en-field">
    <div>
        <h3>
            <%- name %>
            <span>
                <a href="#fields/edit/<%- id %>"><span class="dashicons dashicons-edit field-dashicon"></span></a>
                <a href="#fields" class="delete-field"><span class="dashicons dashicons-trash field-dashicon"></span></a>
            </span>
        </h3>
    </div>
    <div class="media-body">
        <dl>
            <dt>Mandatory:</dt>
            <dd><input type="checkbox" disabled="disabled" class="form-control" <% print( mandatory ?  'checked': '') %>></dd>
            <dt>Label:</dt>
            <dd><%- label %></dd>
            <dt>Type:</dt>
            <dd><%- type %></dd>
        </dl>
    </div>
</script>

<script type="text/html" id="tmpl-new-en-field">
    <hr>
    <h4><%= id <= 0 ? 'Create Field' : 'Edit Field ' + name %> </h4>
    <form role="form" class="form-horizontal field-form">
        <div class="form-group">
            <label class="col-md-3 control-label">Field name:</label>
            <div class="col-md-3">
                <input type="text" class="form-control field-name-input" value="<%- name %>" id="en_field_name">
            </div>
        </div>
        <div class="form-group">
            <label class="col-md-3 control-label">Is Mandatory:</label>
            <div class="col-md-3">
                <input type="checkbox" class="form-control field-mandatory-checkbox" id="en_field_mandatory" <% print( mandatory ?  'checked': '') %>>
            </div>
        </div>
        <div class="form-group">
            <label class="col-md-3 control-label">Label:</label>
            <div class="col-md-3">
                <input type="text" class="form-control field-label-input" value="<%- label %>" id="en_field_label">
            </div>
        </div>
        <div class="form-group">
            <label class="col-md-3 control-label">Type:</label>
            <div class="col-md-3">
                <select class="form-control field-type-select" id="en_field_type">
                 <option value="0">--Select--</option>
                 <option value="1">Text</option>
                 <option value="2">Country selector</option>
                 <option value="3">Question</option>
                </select>
            </div>
        </div>
        <div class="form-group">
            <div class="col-md-2">
                <button class="button button-primary <%= id <= 0 ? 'add-field' : 'edit-field' %>"  <%= id > 0 ? 'data-id="' + id +'"' : '' %> >Submit</button>
                <a href="#fields" class="button">Cancel</a>
            </div>
        </div>
    </form>
</script>