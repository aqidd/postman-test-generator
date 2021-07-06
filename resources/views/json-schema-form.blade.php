<!DOCTYPE html>
<html>
<head>
    <title>Postman Test Generator</title>
    <link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
     <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <title>Postman Test Generator</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.0/jquery.min.js"></script>
    <script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="http://cdnjs.cloudflare.com/ajax/libs/jquery.bootstrapvalidator/0.5.3/css/bootstrapValidator.min.css"/>
    <script type="text/javascript" src="http://cdnjs.cloudflare.com/ajax/libs/jquery.bootstrapvalidator/0.5.3/js/bootstrapValidator.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bulma/0.3.1/css/bulma.min.css" />
    <script src="https://unpkg.com/vue@2.1.10/dist/vue.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.15.3/axios.min.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.3/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.3/toastr.min.js"></script>
    <style>
        pre {
           background-color: ghostwhite;
           border: 1px solid silver;
           padding: 10px;
           }
        .json-key {
           color: brown;
           }
        .json-value {
           color: navy;
           }
        .json-string {
           color: olive;
           }
        .title{
            text-align: center;
            margin: 30px auto;
            font-size: 33px;
            font-weight: bold;
        }
        .profile{
            margin: 30px 0;
        }
        .submit-button{
            margin-top: 10px;
        }
    </style>
</head>
<body>
<div id="generator">
    <h1 class="title">Postman Test Generator</h1>
    <div class="container">
        <div class="columns">
            <div class="column">
                <form method="post" action="/request_json" autocomplete="off">
                    <div v-if="errors">
                        <div class="alert alert-danger">
                            <ul>
                                <li v-for="error in errors.data.json">@{{ error }}</li>
                                <li v-for="error in errors.data.response_time">@{{ error }}</li>
                            </ul>
                        </div>
                    </div>
                    <input id="deta" type="hidden" name="_token" value="{{ csrf_token() }}">

                    <label for="details">JSON:</label>
                    <textarea v-model="json" name="details" id="details" class="form-control" placeholder="JSON" rows="25" ></textarea>
                    <p><label>Response Time:</label>
                    <input type="text" v-model="response_time" name="response" class="form-control input" size="30" placeholder="Response Time"></p>
                    <div class="form-group submit-button">
                        <button type="button" class="btn btn-success" v-on:click="$event.preventDefault();request_data()" name="commit">Submit </button>
                    </div>
                </form>
            </div>
            <div class="column">
                <div v-if="response">
                    <div v-html="response"></div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    axios.defaults.headers.common['X-CSRF-TOKEN'] = $('meta[name="csrf-token"]').attr('content');
    data = new Vue({
        el: '#generator',
        data: {
            response: null,
            json: null,
            response_time: null,
            errors: null,
            project_name: null,
            save_error: null,
            projects: null,
            project_chooser:""
        },
        mounted: function(){
            this.get_project();
        },
        methods:{
            request_data:function(){
                this.response = null;
                this.errors = null;
                axios.post('request_json',{
                    json: this.json,
                    response_time: this.response_time
                }).then(function(response){
                    console.log(response);
                    this.response = response.data;
                }.bind(this)).catch(function(errors){
                    this.errors = errors.response;
                    console.log(errors.response);
                }.bind(this));
            },
            save_project:function(){
                this.save_error = null;
                axios.post('project',{
                    name: this.project_name,
                    json: this.json,
                    response_time: this.response_time
                }).then(function(response){
                    console.log('ok');
                    toastr.success('Saving success');
                    this.get_project();
                }.bind(this)).catch(function(errors){
                    this.save_error = errors.response;
                    console.log(errors.response);
                }.bind(this));
            },
            get_project:function(){
                 axios.get('project')
                    .then(function(response){
                        this.projects = response.data;
                    }.bind(this));
            },
            choose_project:function(){
                this.json = this.projects[this.project_chooser].json;
                this.response_time = this.projects[this.project_chooser].response_time;
                this.request_data();
            }
        }
    })
</script>
</body>
</html>
