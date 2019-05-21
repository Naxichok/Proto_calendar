<div id="modalEvent" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Modal title</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <div class="row">
                <div class="col-lg-12 form-group">
                    <label for="title">
                        Titre : 
                    </label>
                    <input id="title" class="form-control" type="text"/>
                    
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <label>
                        Date Début :
                        <div class="form-group">
                            <div class="datetimepicker input-group date" id="datetimepicker1" data-target-input="nearest">
                                <input id="date_start" type="text" class="form-control datetimepicker-input" data-target="#datetimepicker1"/>
                                <div class="input-group-append" data-target="#datetimepicker1" data-toggle="datetimepicker">
                                    <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                </div>
                            </div>
                        </div>
                    </label>
                </div>
                <div class="col-lg-6">
                    <label>
                        Date fin :
                        <div class="datetimepicker input-group date" id="datetimepicker2" data-target-input="nearest">
                            <input id="date_end" type="text" class="form-control datetimepicker-input" data-target="#datetimepicker2"/>
                            <div class="input-group-append" data-target="#datetimepicker2" data-toggle="datetimepicker">
                                <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                            </div>
                        </div>
                    </label>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6 form-group">
                    <label>
                        Clé : 
                        <select id="key" class="form-control">
                            <?php
                                foreach($keys as $key)
                                {
                                    echo '<option value="'.$key['id_key'].'">'.$key['name_key'].'</option>';
                                }
                            ?>
                        </select>                
                    </label>
                </div>
                <div class="col-lg-6 form-group">
                    <label>
                        Utilisateur : 
                        <select id='user' class="form-control">
                            <?php 
                                foreach($users as $user)
                                {
                                    echo '<option value="'.$user['id_user'].'">'.$user['lastname_user'].' '.$user['firstname_user'].'</option>';
                                }
                            ?>
                        
                        </select>
                    </label>
                </div>
            </div>

            
        </div>
        <div class="modal-footer">
            <button id="save" type="button" class="btn btn-primary">Enregistrer les modifications</button>
            <button id="delete" type="button" class="btn btn-danger">Supprimer l'évenement</button>
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
        </div>
    </div>
  </div>
</div>