/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */
/**
 * ilCloudFileList
 * @author  Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 *
 * **/

function ilCloudFileList(url_get_block, url_create_folder, url_upload_file, url_delete_item, root_id, root_path, current_id, current_path, max_file_size_txt) {

    var DEBUG = true;

    var url_get_block = url_get_block;
    var url_create_folder = url_create_folder;
    var url_upload_file = url_upload_file;
    var url_delete_item = url_delete_item;

    var parent_id = root_id;
    var root_id = root_id;
    var current_id = current_id;
    var root_path = root_path;
    var current_path = current_path;
    var max_file_size_txt = max_file_size_txt;

    //To prevent double clicks
    var clicked_create_folder = false;
    var clicked_upload_item = false;
    var clicked_delete_item = false;

    //store variable to keep access to "this"
    var self = this;
    var uploading = false;

    this.showDebugMessage = function (message) {
        if (DEBUG === true) {
            console.log("cld: "+message);
        }
    }

    this.getCurrentId = function () {
        return current_id;
    }

    this.setRootPath = function (path) {
        root_path = path;
    }
    //show Block with Id
    this.showBlock = function (id) {
        $('#xcld_block_' + id).show();
        $('#xcld_locator_' + id).show();
    }

    //hide Block with id
    this.hideBlock = function (id) {
        $('#xcld_block_' + id).hide();
    }

    //show Block with Id
    this.removeBlock = function (id) {
        $('#xcld_block_' + id).remove();
        $('#xcld_locator_' + id).remove();
    }


    this.showItemCreationList = function () {
        $("#xcld_toolbar").show();
        $("#il_add_new_cld_item_v").show();
    }

    this.hideItemCreationList = function () {
        $("#xcld_toolbar").hide();
        $("#il_add_new_cld_item_v").hide();
    }

    this.toggleTabs = function () {
        if (current_id == root_id) {
            $("#tab_settings").show();
            $("#tab_id_permissions").show();
            $("#tab_id_info").show();
        }
        else {
            $("#tab_settings").hide();
            $("#tab_id_permissions").hide();
            $("#tab_id_info").hide();
        }
    }

    this.hideMessage = function () {
        self.showDebugMessage("hideMessage");
        $("#xcld_message").hide();
    }
    this.showMessage = function (message) {
        self.showDebugMessage("showMessage");
        $("#xcld_message").html(message);
        $("#xcld_message").show();
        display_message = true;
    }

    this.showCurrent = function (show_message, callback, data) {
        if(!show_message)
        {
            this.hideMessage();
        }

        //Update PermaLink
	    perm_link = $("#current_perma_link").val();
	    escaped_current_path = current_path.replace(/\//g, "_");
	    perm_link = perm_link.replace(/path_.*_endPath/,"path_"+ escaped_current_path+"_endPath");
	    $("#current_perma_link").val(perm_link);

	    //Show or hide Tabs
        this.toggleTabs();

        //Check if block already exists as hidden html block (if it was drawn previously) if so, just show it again.
        if ($("#xcld_block_" + current_id).length > 0) {
            this.showDebugMessage("showCurrent: Block already exists, not going Ajax. id=" + current_id);
            $('.xcld_locator').hide();
            this.showBlock(current_id);
            this.hideProgressAnimation();
            if (callback instanceof Function) {
                callback(this, data);
            }
        }
        //If not load it with ajax
        else {
            this.showProgressAnimation();
            $.ajax({
                type: "POST",
                url: url_get_block.replace(/&amp;/g, '&'),
                data: {'id': current_id, 'path':current_path}
            }).done(function (return_data) {
                    if (return_data.success) {
                        self.showDebugMessage("showCurrent: Block did not exist, successfull ajax request. id=" + current_id + " path= " + current_path);
                        $("#xcld_blocks").append(return_data.content);
                        $('.xcld_locator').hide();
                        $('.ilToolbarSeparator').before(return_data.locator);
                    }
                    else {
                        self.showDebugMessage("showCurrent: Block did not exist, not successfull ajax request. id=" + current_id + " path= " + current_path);

                        if (return_data.message) {
                            self.showDebugMessage("showCurrent: Block did not exist, not successfull ajax request. message=" + return_data.message);
                            self.showMessage(return_data.message);
                        }
                        else {
                            self.showDebugMessage("showCurrent: Block did not exist, not successfull ajax request. data=" + return_data);
                            self.showMessage(return_data);
                        }

                        self.hideItemCreationList();
                    }
                    if (callback instanceof Function) {
                        callback(self, data);
                    }
                    self.hideProgressAnimation();
                });
        }
    }

    this.deleteItem = function (id) {
        if(!this.clicked_delete_item)
        {
            this.clicked_delete_item = true;

            this.hideMessage();
            $.ajax({
                type: "POST",
                url: url_delete_item.replace(/&amp;/g, '&'),
                data: {'id': id}
            }).done(function (return_data) {
                    self.clicked_delete_item = false;
                    if (return_data.success) {
                        self.showDebugMessage("deleteItem: Form successfully created per ajax. id=" + current_id + " path= " + current_path);
                        self.hideBlock(current_id);
                        self.hideItemCreationList();
                        $("#xcld_blocks").append(return_data.content);
                        $("input[name='cmd[deleteItem]']").click(function () {
                            self.showProgressAnimation();
                        });
                    }

                    else {
                        if (return_data.message) {
                            self.showDebugMessage("deleteItem: Form not successfully created per ajax. message=" + return_data.message);
                            self.showMessage(return_data.message);
                        }
                        else {
                            self.showDebugMessage("deleteItem: Form not successfully created per ajax. data=" + return_data);
                            self.showMessage(return_data);
                        }
                    }
                });
        }
    }


    this.afterDeleteItem = function (data) {
        this.clicked_delete_item = false;
        if (data.success || data.status == "cancel") {
            var callback = function (self, data) {
                $("#cld_delete_item").remove();
                self.showItemCreationList();

                if (data.status == "cancel") {
                    self.showDebugMessage("afterDeleteItem: Deletion cancelled.");
                }
                else if (data.success) {
                    self.showDebugMessage("afterDeleteItem: Item successfully deleted.");
                    self.showMessage(data.message);
                }
            }
            if(data.success)
            {
                self.removeBlock(current_id);
                this.showCurrent(true, callback, data);
            }
            else
            {
                this.showCurrent(false, callback, data);
            }
        }
        else {
            if (data.message) {
                self.showDebugMessage("afterDeleteItem: Deletion of item failed. message=" + data.message);
                self.showMessage(data.message);
            }
            else {
                self.showDebugMessage("afterDeleteItem: Deletion of Item failed. data=" + data);
                self.showMessage(data);

            }
            display_message = false;
            self.hideProgressAnimation();
        }


    }

    //Ajax request to create a new Folder
    this.createFolder = function () {

        if(!this.clicked_create_folder)
        {
            this.clicked_create_folder = true;
            this.hideMessage();

            $.ajax({
                type: "POST",
                url: url_create_folder.replace(/&amp;/g, '&'),
                data: { 'id': current_id}
            }).done(function (return_data) {
                    if (return_data.success) {
                        self.clicked_create_folder = true;
                        self.showDebugMessage("createFolder: Form successfully created per ajax. id=" + current_id + " path= " + current_path);
                        self.hideBlock(current_id);
                        self.hideItemCreationList();
                        $("#xcld_blocks").append(return_data.content);
                        $("input[name='cmd[createFolder]']").click(function () {
                            self.showProgressAnimation();
                        });
                    }
                    else {
                        if (return_data.message) {
                            self.showDebugMessage("createFolder: Form not successfully created per ajax. message=" + return_data.message);
                            self.showMessage(return_data.message);
                        }
                        else {
                            self.showDebugMessage("createFolder: Form not successfully created per ajax. data=" + return_data);
                            self.showMessage(return_data);
                        }
                    }
                });
        }
    }

    this.afterCreateFolder = function (data) {
        self.clicked_create_folder = false;
        if (data.success || data.status == "cancel") {
            var callback = function (self, data) {
                $("#form_cld_create_folder").remove();
                self.showItemCreationList();

                if (data.status == "cancel") {
                    self.showDebugMessage("afterCreateFolder: Creation cancelled.");
                }
                else if (data.success) {
                    self.showDebugMessage("afterCreateFolder: Folder successfully created.");
                    window.location.hash = $("#xcld_folder_" + data.folder_id).find("a:first").attr('href');
                    self.showMessage(data.message);
                }
            }
            if (data.success) {
                self.removeBlock(current_id);
                this.showCurrent(true, callback, data);
            }
            else {
                this.showCurrent(false, callback, data);
            }
        }
        else {
            if (data.message) {
                self.showDebugMessage("afterCreateFolder: Creation of folder failed. message=" + data.message);
                self.showMessage(data.message);
            }
            else {
                self.showDebugMessage("afterCreateFolder: Creation of folder failed. data=" + data);
                self.showMessage(data);

            }
            display_message = false;
            self.hideProgressAnimation();
        }
    }

    //Ajax request to create a new Folder
    this.uploadFile = function () {
        if(!this.clicked_upload_item)
        {
            this.clicked_upload_item = true;
            uploading = true;
            this.hideMessage();
            this.showProgressAnimation();
            $.ajax({
                type: "POST",
                url: url_upload_file.replace(/&amp;/g, '&'),
                data: { 'folder_id': current_id}
            }).done(function (return_data) {
                    self.hideProgressAnimation();
                    self.removeBlock(current_id);
                    self.hideItemCreationList();
                    $("#xcld_blocks").append(return_data);
                    $(".ilFileUploadToggleOptions").click(function () {
                        $(".ilFileUploadEntryDescription").remove();
                    });
                    $(".ilFileUploadContainer").children(".ilFormInfo").html(max_file_size_txt);
                });
        }
    }

    this.afterUpload = function (message) {
        self.clicked_upload_item = false;
        self.showDebugMessage("afterUpload: "+ message);
        this.showCurrent(false, function (self) {
            $("#form_upload").remove();
            self.showItemCreationList();

            uploading = false;
        });
    }

    this.showProgressAnimation = function () {
        $("#loading_div_background").show();
    }
    this.hideProgressAnimation = function () {
        $("#loading_div_background").hide();
    }

    $("#xcld_message").insertBefore("#xcld_toolbar");

    /** if Errors or var_dumps occured in an Ajax request, they might have been sent to the cld_blank_target iframe.
     * If so, display them
     */
    $('iframe#cld_blank_target').load(function () {
        var iframeBody = this.contentDocument.body;
        if($(iframeBody).html() != "")
        {
            self.showDebugMessage("an unknown occured. message: " + $(iframeBody).html());
            self.showMessage($(iframeBody).html());
            self.hideProgressAnimation();
        }
    });

    $.address.change(function (event) {
        if (event.pathNames[0] == "delete_item") {
            self.deleteItem(event.parameters.id);
        }
        else if (uploading == false) {
            self.hideBlock(current_id);
            event.parameters.current_id ? current_id = event.parameters.current_id : current_id = current_id;
            event.parameters.current_path ? current_path = decodeURIComponent((event.parameters.current_path + '').replace(/\+/g, '%20')) : current_path = current_path;

            event.parameters.parent_id ? parent_id = event.parameters.parent_id : parent_id = root_id;
            self.hideBlock(parent_id);
            self.showCurrent(event.parameters.show_message);
        }
        self.showDebugMessage("address.change: Change of address notified. event: " + event.pathNames[0] + " id: " + current_id);
    });


}

