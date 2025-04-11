$(document).ready(function(){
    let RoleID="";
    let cruds={
        add:false,
        edit:false
    }
    const RootUrl=$('#txtRootUrl').val();
    const getUserRightsTable=()=>{
        let html="";
        
        html+='<div class="row justify-content-center">';
            html+='<div class="col-sm-4">';
                html+='<div class="form-group">';
                    html+='<label for="txtRoleName">Role Name <span class="required">*</span></label>';
                    html+='<input type="text" id="txtRoleName" class="form-control" placeholder="Role Name"  value="">';
                    html+='<span class="errors" id="txtRoleName-err"></span>';
                html+='</div>';
            html+='</div>';
        html+='</div>';
        html+='<div class="row justify-content-center">';
            html+='<div class="col-sm-12">';
                html+='<table class="table" id="tblUserRights">';
                    html+='<thead>';
                        html+='<tr>';
                            html+='<th class="text-center">Modules</th>';
                            html+='<th class="text-center">ADD <div class="checkbox checkbox-dark" style="display:none"><input id="chkAddAll" type="checkbox" ><label for="chkAddAll"></label></div></th>';
                            html+='<th class="text-center">VIEW <div class="checkbox checkbox-dark" style="display:none"><input id="chkViewAll" type="checkbox" ><label for="chkViewAll"></label></div></th>';
                            html+='<th class="text-center">EDIT <div class="checkbox checkbox-dark" style="display:none"><input id="chkEditAll" type="checkbox"  ><label for="chkEditAll"></label></div></th>';
                            html+='<th class="text-center">DELETE <div class="checkbox checkbox-dark" style="display:none"><input id="chkDeleteAll" type="checkbox" ><label for="chkDeleteAll"></label></div></th>';
                            html+='<th class="text-center">COPY <div class="checkbox checkbox-dark" style="display:none"><input id="chkCopyAll" type="checkbox" ><label for="chkCopyAll"></label></div></th>';
                            html+='<th class="text-center">EXCEL <div class="checkbox checkbox-dark" style="display:none"><input id="chkExcelAll" type="checkbox" ><label for="chkExcelAll"></label></div></th>';
                            html+='<th class="text-center">CSV <div class="checkbox checkbox-dark" style="display:none"><input id="chkCSVAll" type="checkbox" ><label for="chkCSVAll"></label></div></th>';
                            html+='<th class="text-center">PRINT <div class="checkbox checkbox-dark" style="display:none"><input id="chkPrintAll" type="checkbox" ><label for="chkPrintAll"></label></div></th>';
                            html+='<th class="text-center">PDF <div class="checkbox checkbox-dark" style="display:none"><input id="chkPDFAll" type="checkbox" ><label for="chkPDFAll"></label></div></th>';
                            html+='<th class="text-center">Restore <div class="checkbox checkbox-dark" style="display:none"><input id="chkRestoreAll" type="checkbox" ><label for="chkRestoreAll"></label></div></th>';
                            html+='<th class="text-center">Show Pwd <div class="checkbox checkbox-dark" style="display:none"><input id="chkShowPwdAll" type="checkbox" ><label for="chkShowPwdAll"></label></div></th>';
                        html+='</tr>';
                    html+='</thead>';
                    html+='<tbody id="tblroles">';
                    html+='</tbody>';
                html+='</table>';
            html+='</div>';
        html+='</div>';
        html+='<div class="row justify-content-end">';
            html+='<div class="col-sm-4 text-right  mt-30">';
                html+='<button class="btn btn-sm btn-outline-dark mr-10" id="btnCloseModal1">Cancel</button>';
                html+='<button class="btn btn-sm btn-outline-success mr-10" id="btnCreateNewRole">Create New Role</button>';
                if(cruds.edit && RoleID !=""){
                    html+='<button class="btn btn-sm btn-outline-info mr-10" id="btnUpdateRole">Update This Role</button>';
                }
                
            html+='</div>';
        html+='</div>';
        return html;
    }
    const getMenus=async()=>{
        
        $.ajax({
            type:"post",
            url:RootUrl+"users-and-permissions/user-roles/get/menus-data",
            headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
            data:{},
            async:false,
            dataType:"json",
            error:function(e, x, settings, exception){ajaxErrors(e, x, settings, exception);},
            success:async function(response){
                await loadMenus(response);
                getUserRoleInfo()

                
            }
        });
    }
    const EnableDisable=async(Slug,ParentSlug)=>{
        var ElemID="chk"+Slug+"View";
        var EditID="chk"+Slug+"Edit";
        var DeleteID="chk"+Slug+"Delete";
        var CopyID="chk"+Slug+"Copy";
        var ExcelID="chk"+Slug+"Excel";
        var CSVID="chk"+Slug+"CSV";
        var PrintID="chk"+Slug+"Print";			
        var PDFID="chk"+Slug+"PDF";		
        var RestoreID="chk"+Slug+"Restore";
        var ShowPwdID="chk"+Slug+"ShowPwd";

        
        var Edit_CRUD_Value=parseInt($('#'+EditID).attr('data-crud-value'));
        var Delete_CRUD_Value=parseInt($('#'+DeleteID).attr('data-crud-value'));
        var Copy_CRUD_Value=parseInt($('#'+CopyID).attr('data-crud-value'));
        var Excel_CRUD_Value=parseInt($('#'+ExcelID).attr('data-crud-value'));
        var CSV_CRUD_Value=parseInt($('#'+CSVID).attr('data-crud-value'));
        var Print_CRUD_Value=parseInt($('#'+PrintID).attr('data-crud-value'));
        var PDF_CRUD_Value=parseInt($('#'+PDFID).attr('data-crud-value'));
        var Restore_CRUD_Value=parseInt($('#'+RestoreID).attr('data-crud-value'));
        var ShowPwd_CRUD_Value=parseInt($('#'+ShowPwdID).attr('data-crud-value'));
        
        if($('#'+ElemID).prop('checked')==true){
            if(Edit_CRUD_Value==1){$('#'+EditID).removeAttr('disabled');}
            if(Delete_CRUD_Value==1){$('#'+DeleteID).removeAttr('disabled');}
            if(Copy_CRUD_Value==1){$('#'+CopyID).removeAttr('disabled');}
            if(Excel_CRUD_Value==1){$('#'+ExcelID).removeAttr('disabled');}
            if(CSV_CRUD_Value==1){$('#'+CSVID).removeAttr('disabled');}
            if(Print_CRUD_Value==1){$('#'+PrintID).removeAttr('disabled');}
            if(PDF_CRUD_Value==1){$('#'+PDFID).removeAttr('disabled');}
            if(Restore_CRUD_Value==1){$('#'+RestoreID).removeAttr('disabled');}
            if(ShowPwd_CRUD_Value==1){$('#'+ShowPwdID).removeAttr('disabled');}
            //Checked
            if($('#'+EditID).attr('data-checked')=="1"){$('#'+EditID).prop('checked',true);}
            if($('#'+DeleteID).attr('data-checked')=="1"){$('#'+DeleteID).prop('checked',true);}
            if($('#'+CopyID).attr('data-checked')=="1"){$('#'+CopyID).prop('checked',true);}
            if($('#'+ExcelID).attr('data-checked')=="1"){$('#'+ExcelID).prop('checked',true);}
            if($('#'+CSVID).attr('data-checked')=="1"){$('#'+CSVID).prop('checked',true);}
            if($('#'+PrintID).attr('data-checked')=="1"){$('#'+PrintID).prop('checked',true);}
            if($('#'+PDFID).attr('data-checked')=="1"){$('#'+PDFID).prop('checked',true);}
            if($('#'+RestoreID).attr('data-checked')=="1"){$('#'+RestoreID).prop('checked',true);}
            if($('#'+ShowPwdID).attr('data-checked')=="1"){$('#'+ShowPwdID).prop('checked',true);}

            if((ParentSlug!=null)||(ParentSlug!="")){
                $('#chk'+ParentSlug+'Edit').removeAttr('disabled');
                $('#chk'+ParentSlug+'Delete').removeAttr('disabled');
                $('#chk'+ParentSlug+'Copy').removeAttr('disabled');
                $('#chk'+ParentSlug+'Excel').removeAttr('disabled');
                $('#chk'+ParentSlug+'CSV').removeAttr('disabled');
                $('#chk'+ParentSlug+'Print').removeAttr('disabled');
                $('#chk'+ParentSlug+'PDF').removeAttr('disabled');
                $('#chk'+ParentSlug+'Restore').removeAttr('disabled');
                $('#chk'+ParentSlug+'ShowPwd').removeAttr('disabled');
            }
        }else{
            if(Edit_CRUD_Value==1){
                $('#'+EditID).attr('disabled','disabled');
                $('#'+EditID).prop('checked',false);
            }
            if(Delete_CRUD_Value==1){
                $('#'+DeleteID).attr('disabled','disabled');
                $('#'+DeleteID).prop('checked',false);
            }

            if(Copy_CRUD_Value==1){$('#'+CopyID).attr('disabled','disabled');$('#'+CopyID).prop('checked',false);}
            if(Excel_CRUD_Value==1){$('#'+ExcelID).attr('disabled','disabled');$('#'+ExcelID).prop('checked',false);}
            if(CSV_CRUD_Value==1){$('#'+CSVID).attr('disabled','disabled');$('#'+CSVID).prop('checked',false);}
            if(Print_CRUD_Value==1){$('#'+PrintID).attr('disabled','disabled');$('#'+PrintID).prop('checked',false);}
            if(PDF_CRUD_Value==1){$('#'+PDFID).attr('disabled','disabled');$('#'+PDFID).prop('checked',false);}
            if(Restore_CRUD_Value==1){$('#'+RestoreID).attr('disabled','disabled');$('#'+RestoreID).prop('checked',false);}
            if(ShowPwd_CRUD_Value==1){$('#'+ShowPwdID).attr('disabled','disabled');$('#'+ShowPwdID).prop('checked',false);}
            if((ParentSlug!=null)||(ParentSlug!="")){
                $('#chk'+ParentSlug+'Edit').attr('disabled',true);
                $('#chk'+ParentSlug+'Delete').attr('disabled',true);
                $('#chk'+ParentSlug+'Copy').attr('disabled',true);
                $('#chk'+ParentSlug+'Excel').attr('disabled',true);
                $('#chk'+ParentSlug+'CSV').attr('disabled',true);
                $('#chk'+ParentSlug+'Print').attr('disabled',true);
                $('#chk'+ParentSlug+'PDF').attr('disabled',true);
                $('#chk'+ParentSlug+'Restore').attr('disabled',true);
                $('#chk'+ParentSlug+'ShowPwd').attr('disabled',true);
            }
        }
    }
	const loadMenus=async(response,lastIndex,Previous,ParentID,ParentSlug)=>{
		var html='';
		for(i=0;i<response.length;i++){
			if((response[i].Slug!="logout")&&(response[i].Slug!="password-change")){
				if(response[i].hasSubMenu==1){
					var html='';
					html+='<tr>';
						html+='<td data-role="MenuGroup"data-parant-id="" data-parent-slug="" data-id="'+response[i].MID+'" data-slug="'+response[i].Slug+'" style="font-weight:700;">'+response[i].MenuName+'</td>';
						html+='<td class="text-center"><div class="checkbox checkbox-primary"><input data-id="'+response[i].MID+'" data-slug="'+response[i].Slug+'" data-crud-value="1" data-crud="Add" class="chkAddAll chkClick" id="chk'+response[i].Slug+'Add" type="checkbox" ><label for="chk'+response[i].Slug+'Add"></label></div></td>';
						html+='<td class="text-center"><div class="checkbox checkbox-primary"><input type="checkbox" data-id="'+response[i].MID+'" data-slug="'+response[i].Slug+'" data-crud-value="1" data-crud="View" class="chkViewAll chkClick" id="chk'+response[i].Slug+'View" ><label for="chk'+response[i].Slug+'View"></label></div></td>';
						html+='<td class="text-center"><div class="checkbox checkbox-primary"><input type="checkbox" data-id="'+response[i].MID+'" data-slug="'+response[i].Slug+'" data-crud-value="1" data-crud="Edit" class="chkEditAll chkClick" id="chk'+response[i].Slug+'Edit" ><label for="chk'+response[i].Slug+'Edit"></label></div></td>';
						html+='<td class="text-center"><div class="checkbox checkbox-primary"><input type="checkbox" data-id="'+response[i].MID+'" data-slug="'+response[i].Slug+'" data-crud-value="1" data-crud="Delete" class="chkDeleteAll chkClick" id="chk'+response[i].Slug+'Delete" ><label for="chk'+response[i].Slug+'Delete"></label></div></td>';
						html+='<td class="text-center"><div class="checkbox checkbox-primary"><input type="checkbox" data-id="'+response[i].MID+'" data-slug="'+response[i].Slug+'" data-crud-value="1" data-crud="Copy" class="chkCopyAll chkClick" id="chk'+response[i].Slug+'Copy" ><label for="chk'+response[i].Slug+'Copy"></label></div></td>';
						html+='<td class="text-center"><div class="checkbox checkbox-primary"><input type="checkbox" data-id="'+response[i].MID+'" data-slug="'+response[i].Slug+'" data-crud-value="1" data-crud="Excel" class="chkExcelAll chkClick" id="chk'+response[i].Slug+'Excel" ><label for="chk'+response[i].Slug+'Excel"></label></div></td>';
						html+='<td class="text-center"><div class="checkbox checkbox-primary"><input type="checkbox" data-id="'+response[i].MID+'" data-slug="'+response[i].Slug+'" data-crud-value="1" data-crud="CSV" class="chkCSVAll chkClick" id="chk'+response[i].Slug+'CSV" ><label for="chk'+response[i].Slug+'CSV"></label></div></td>';
						html+='<td class="text-center"><div class="checkbox checkbox-primary"><input type="checkbox" data-id="'+response[i].MID+'" data-slug="'+response[i].Slug+'" data-crud-value="1" data-crud="Print" class="chkPrintAll chkClick" id="chk'+response[i].Slug+'Print" ><label for="chk'+response[i].Slug+'Print"></label></div></td>';
						html+='<td class="text-center"><div class="checkbox checkbox-primary"><input type="checkbox" data-id="'+response[i].MID+'" data-slug="'+response[i].Slug+'" data-crud-value="1" data-crud="PDF" class="chkPDFAll chkClick" id="chk'+response[i].Slug+'PDF" ><label for="chk'+response[i].Slug+'PDF"></label></div></td>';
						html+='<td class="text-center"><div class="checkbox checkbox-primary"><input type="checkbox" data-id="'+response[i].MID+'" data-slug="'+response[i].Slug+'" data-crud-value="1" data-crud="Restore" class="chkPDFAll chkClick" id="chk'+response[i].Slug+'Restore" ><label for="chk'+response[i].Slug+'Restore"></label></div></td>';
						html+='<td class="text-center"><div class="checkbox checkbox-primary"><input type="checkbox" data-id="'+response[i].MID+'" data-slug="'+response[i].Slug+'" data-crud-value="1" data-crud="ShowPwd" class="chkShowPwdAll chkClick" id="chk'+response[i].Slug+'ShowPwd" ><label for="chk'+response[i].Slug+'ShowPwd"></label></div></td>';

					html+='</tr>';
					$('#tblroles').append(html);
					let sub=await loadMenus(response[i]['SubMenu'],i,html,response[i].MID,response[i].Slug);
					i=sub.LastIndex;
				}else{
					var html='';
					var style="";
					var disabled="";
					var Dashboard="";
					if((response[i].ParentID=="")||(response[i].ParentID==null)){
						style="font-weight:700;";
					}else{
						style="padding-left:50px;";MenuGroup="Menu";
					}
					if((response[i].Slug=="change-password")||(response[i].Slug=="profile")||(response[i].Slug=="dashboard")){
						Dashboard="checked='true' disabled";
					}
					html+='<tr>';
						html+='<td data-role="menu" data-parant-id="'+ParentID+'" data-parent-slug="'+ParentSlug+'" data-id="'+response[i].MID+'" data-slug="'+response[i].Slug+'" style="'+style+'">'+response[i].MenuName+'</td>';
						if(response[i].Crud.Add==0){disabled="disabled"}else{disabled="";}
						html+='<td class="text-center"><div class="checkbox checkbox-success"><input type="checkbox" '+disabled+' data-id="'+response[i].MID+'" data-slug="'+response[i].Slug+'" data-parant-id="'+ParentID+'" data-parent-slug="'+ParentSlug+'" data-crud-value="'+response[i].Crud['Add']+'" data-crud="Add" class="chkAddAll MenuClick chk'+ParentSlug+'Add"  id="chk'+response[i].Slug+'Add" ><label for="chk'+response[i].Slug+'Add"></label></div></td>';
						
							
						if(response[i].Crud['View']==0){disabled="disabled"}else{disabled="";}
						html+='<td class="text-center"><div class="checkbox checkbox-success"><input type="checkbox" '+Dashboard+' '+disabled+' data-id="'+response[i].MID+'" data-slug="'+response[i].Slug+'" data-parant-id="'+ParentID+'" data-parent-slug="'+ParentSlug+'" data-crud-value="'+response[i].Crud['View']+'" data-crud="View" class="chkViewAll MenuClick chk'+ParentSlug+'View" id="chk'+response[i].Slug+'View" ><label for="chk'+response[i].Slug+'View"></label></div></td>';

						if(response[i].Crud['Edit']==0){disabled="disabled"}else{disabled="";}
						html+='<td class="text-center"><div class="checkbox checkbox-success"><input type="checkbox" '+disabled+' data-id="'+response[i].MID+'" data-slug="'+response[i].Slug+'" data-parant-id="'+ParentID+'" data-parent-slug="'+ParentSlug+'" data-crud-value="'+response[i].Crud['Edit']+'" data-crud="Edit" class="chkEditAll MenuClick chk'+ParentSlug+'Edit" id="chk'+response[i].Slug+'Edit" ><label for="chk'+response[i].Slug+'Edit"></label></div></td>';


						if(response[i].Crud['Delete']==0){disabled="disabled"}else{disabled="";}
						html+='<td class="text-center"><div class="checkbox checkbox-success"><input type="checkbox" '+disabled+' data-id="'+response[i].MID+'" data-slug="'+response[i].Slug+'" data-parant-id="'+ParentID+'" data-parent-slug="'+ParentSlug+'" data-crud-value="'+response[i].Crud['Delete']+'" data-crud="Delete" class="chkDeleteAll MenuClick chk'+ParentSlug+'Delete" id="chk'+response[i].Slug+'Delete" ><label for="chk'+response[i].Slug+'Delete"></label></div></td>';


						if(response[i].Crud['Copy']==0){disabled="disabled"}else{disabled="";}
						html+='<td class="text-center"><div class="checkbox checkbox-success"><input type="checkbox" '+disabled+' data-id="'+response[i].MID+'" data-slug="'+response[i].Slug+'" data-parant-id="'+ParentID+'" data-parent-slug="'+ParentSlug+'" data-crud-value="'+response[i].Crud['Copy']+'" data-crud="Copy" class="chkCopyAll MenuClick chk'+ParentSlug+'Copy" id="chk'+response[i].Slug+'Copy" ><label for="chk'+response[i].Slug+'Copy"></label></div></td>';


						if(response[i].Crud['Excel']==0){disabled="disabled"}else{disabled="";}
						html+='<td class="text-center"><div class="checkbox checkbox-success"><input type="checkbox" '+disabled+' data-id="'+response[i].MID+'" data-slug="'+response[i].Slug+'" data-parant-id="'+ParentID+'" data-parent-slug="'+ParentSlug+'" data-crud-value="'+response[i].Crud['Excel']+'" data-crud="Excel" class="chkExcelAll MenuClick chk'+ParentSlug+'Excel" id="chk'+response[i].Slug+'Excel" ><label for="chk'+response[i].Slug+'Excel"></label></div></td>';


						if(response[i].Crud['CSV']==0){disabled="disabled"}else{disabled="";}
						html+='<td class="text-center"><div class="checkbox checkbox-success"><input type="checkbox" '+disabled+' data-id="'+response[i].MID+'" data-slug="'+response[i].Slug+'" data-parant-id="'+ParentID+'" data-parent-slug="'+ParentSlug+'" data-crud-value="'+response[i].Crud['CSV']+'" data-crud="CSV" class="chkCSVAll MenuClick chk'+ParentSlug+'CSV" id="chk'+response[i].Slug+'CSV" ><label for="chk'+response[i].Slug+'CSV"></label></div></td>';


						if(response[i].Crud['Print']==0){disabled="disabled"}else{disabled="";}
						html+='<td class="text-center"><div class="checkbox checkbox-success"><input type="checkbox" '+disabled+' data-id="'+response[i].MID+'" data-slug="'+response[i].Slug+'" data-parant-id="'+ParentID+'" data-parent-slug="'+ParentSlug+'" data-crud-value="'+response[i].Crud['Print']+'" data-crud="Print" class="chkPrintAll MenuClick chk'+ParentSlug+'Print" id="chk'+response[i].Slug+'Print" ><label for="chk'+response[i].Slug+'Print"></label></div></td>';

						if(response[i].Crud['PDF']==0){disabled="disabled"}else{disabled="";}
						html+='<td class="text-center"><div class="checkbox checkbox-success"><input type="checkbox" '+disabled+' data-id="'+response[i].MID+'" data-slug="'+response[i].Slug+'" data-parant-id="'+ParentID+'" data-parent-slug="'+ParentSlug+'" data-crud-value="'+response[i].Crud['PDF']+'" data-crud="PDF" class="chkPDFAll MenuClick chk'+ParentSlug+'PDF" id="chk'+response[i].Slug+'PDF" ><label for="chk'+response[i].Slug+'PDF"></label></div></td>';

						if(response[i].Crud['Restore']==0){disabled="disabled"}else{disabled="";}
						html+='<td class="text-center"><div class="checkbox checkbox-success"><input type="checkbox" '+disabled+' data-id="'+response[i].MID+'" data-slug="'+response[i].Slug+'" data-parant-id="'+ParentID+'" data-parent-slug="'+ParentSlug+'" data-crud-value="'+response[i].Crud['Restore']+'" data-crud="Restore" class="chkRestoreAll MenuClick chk'+ParentSlug+'Restore" id="chk'+response[i].Slug+'Restore" ><label for="chk'+response[i].Slug+'Restore"></label></div></td>';

						if(response[i].Crud['ShowPwd']==0){disabled="disabled"}else{disabled="";}
						html+='<td class="text-center"><div class="checkbox checkbox-success"><input type="checkbox" '+disabled+' data-id="'+response[i].MID+'" data-slug="'+response[i].Slug+'" data-parant-id="'+ParentID+'" data-parent-slug="'+ParentSlug+'" data-crud-value="'+response[i].Crud['ShowPwd']+'" data-crud="ShowPwd" class="chkShowPwdAll MenuClick chk'+ParentSlug+'ShowPwd" id="chk'+response[i].Slug+'ShowPwd" ><label for="chk'+response[i].Slug+'ShowPwd"></label></div></td>';

					html+='</tr>';
					$('#tblroles').append(html);
				}
				EnableDisable(response[i].Slug,ParentSlug);
			}
		}
		return {LastIndex:lastIndex,previous:Previous};
	}
	const MenuGroupStatus=async()=>{
        var myTable 	= document.getElementById('tblUserRights');
        var totrows		= $('#tblUserRights tbody tr').length;
        for(i=2;i<=totrows;i++){
			var elem=myTable.rows[i].cells[0];
            var role=elem.attributes['data-role'].value;
            var id=elem.attributes['data-id'].value;
            var slug=elem.attributes['data-slug'].value;
            var colCount=myTable.rows[i].cells.length;
			if(role=="MenuGroup"){
				//ADD
                var action="Add"
                var Selector="chk"+slug+action;
                var elems=$('.'+Selector);
                var CheckStatus=true;
					
				if(elems.length>0){
					for(j=0;j<elems.length;j++){
						var elem=elems[j];
						var elemid=elem.getAttribute('id');
						var crudvalue=elem.getAttribute('data-crud-value');
						if(crudvalue==1){
							if($('#'+elemid).prop('checked')==false){
								CheckStatus=false;break;
							}
						}
					}
				}
				if(CheckStatus==false){$('#'+Selector+":not(:disabled)").prop('checked',false);}else{$('#'+Selector+":not(:disabled)").prop('checked',true);}
					
					
				//VIEW
                action="View"
                Selector="chk"+slug+action;
                elems=$('.'+Selector);
                CheckStatus=true;
				if(elems.length>0){
					for(j=0;j<elems.length;j++){
						var elem=elems[j];
						var elemid=elem.getAttribute('id');
						var crudvalue=elem.getAttribute('data-crud-value');
						if(crudvalue==1){
							if($('#'+elemid).prop('checked')==false){
								CheckStatus=false;break;
							}
						}
					}
				}
				if(CheckStatus==false){$('#'+Selector+":not(:disabled)").prop('checked',false);}else{$('#'+Selector+":not(:disabled)").prop('checked',true);}
					
					
				//EDIT
                action="Edit"
                Selector="chk"+slug+action;
                elems=$('.'+Selector);
                CheckStatus=true;
				if(elems.length>0){
					for(j=0;j<elems.length;j++){
						var elem=elems[j];
						var elemid=elem.getAttribute('id');
						var crudvalue=elem.getAttribute('data-crud-value');
						if(crudvalue==1){
							if($('#'+elemid).prop('checked')==false){
								CheckStatus=false;break;
							}
						}
					}
				}
				if(CheckStatus==false){$('#'+Selector+":not(:disabled)").prop('checked',false);}else{$('#'+Selector+":not(:disabled)").prop('checked',true);}
					
					
				//DELETE
                action="Delete"
                Selector="chk"+slug+action;
                elems=$('.'+Selector);
                CheckStatus=true;
				if(elems.length>0){
					for(j=0;j<elems.length;j++){
						var elem=elems[j];
						var elemid=elem.getAttribute('id');
						var crudvalue=elem.getAttribute('data-crud-value');
						if(crudvalue==1){
							if($('#'+elemid).prop('checked')==false){
								CheckStatus=false;break;
							}
						}
					}
				}else{
					$('#'+Selector).attr('disabled',true);
					$('#'+Selector).attr('data-crud-value',0);
				}
				if(CheckStatus==false){$('#'+Selector+":not(:disabled)").prop('checked',false);}else{$('#'+Selector+":not(:disabled)").prop('checked',true);}
					
				//COPY
                action="Copy"
                Selector="chk"+slug+action;
                elems=$('.'+Selector);
                CheckStatus=true;
				if(elems.length>0){
					for(j=0;j<elems.length;j++){
						var elem=elems[j];
						var elemid=elem.getAttribute('id');
						var crudvalue=elem.getAttribute('data-crud-value');
						if(crudvalue==1){
							if($('#'+elemid).prop('checked')==false){
								CheckStatus=false;break;
							}
						}
					}
				}else{
					$('#'+Selector).attr('disabled',true);
					$('#'+Selector).attr('data-crud-value',0);
				}
				if(CheckStatus==false){$('#'+Selector+":not(:disabled)").prop('checked',false);}else{$('#'+Selector+":not(:disabled)").prop('checked',true);}
					
				//EXCEL
                action="Excel"
                Selector="chk"+slug+action;
                elems=$('.'+Selector);
                CheckStatus=true;
				if(elems.length>0){
					for(j=0;j<elems.length;j++){
						var elem=elems[j];
						var elemid=elem.getAttribute('id');
						var crudvalue=elem.getAttribute('data-crud-value');
						if(crudvalue==1){
							if($('#'+elemid).prop('checked')==false){
								CheckStatus=false;break;
							}
						}
					}
				}else{
					$('#'+Selector).attr('disabled',true);
					$('#'+Selector).attr('data-crud-value',0);
				}
				if(CheckStatus==false){$('#'+Selector+":not(:disabled)").prop('checked',false);}else{$('#'+Selector+":not(:disabled)").prop('checked',true);}
					
				//CSV
                action="CSV"
                Selector="chk"+slug+action;
                elems=$('.'+Selector);
                CheckStatus=true;
	    		if(elems.length>0){
					for(j=0;j<elems.length;j++){
						var elem=elems[j];
						var elemid=elem.getAttribute('id');
						var crudvalue=elem.getAttribute('data-crud-value');
						if(crudvalue==1){
							if($('#'+elemid).prop('checked')==false){
								CheckStatus=false;break;
							}
						}
					}
				}else{
					$('#'+Selector).attr('disabled',true);
					$('#'+Selector).attr('data-crud-value',0);
				}
				if(CheckStatus==false){$('#'+Selector+":not(:disabled)").prop('checked',false);}else{$('#'+Selector+":not(:disabled)").prop('checked',true);}
					
				//PRINT
                action="Print"
                Selector="chk"+slug+action;
                elems=$('.'+Selector);
                CheckStatus=true;
				if(elems.length>0){
					for(j=0;j<elems.length;j++){
						var elem=elems[j];
						var elemid=elem.getAttribute('id');
						var crudvalue=elem.getAttribute('data-crud-value');
						if(crudvalue==1){
							if($('#'+elemid).prop('checked')==false){
								CheckStatus=false;break;
							}
						}
					}
				}else{
					$('#'+Selector).attr('disabled',true);
					$('#'+Selector).attr('data-crud-value',0);
				}
				if(CheckStatus==false){$('#'+Selector+":not(:disabled)").prop('checked',false);}else{$('#'+Selector+":not(:disabled)").prop('checked',true);}
				
				//PDF
                action="PDF"
                Selector="chk"+slug+action;
                elems=$('.'+Selector);
                CheckStatus=true;
				if(elems.length>0){
					for(j=0;j<elems.length;j++){
						var elem=elems[j];
						var elemid=elem.getAttribute('id');
						var crudvalue=elem.getAttribute('data-crud-value');
						if(crudvalue==1){
							if($('#'+elemid).prop('checked')==false){
								CheckStatus=false;break;
							}
						}
					}
				}else{
					$('#'+Selector).attr('disabled',true);
					$('#'+Selector).attr('data-crud-value',0);
				}
				if(CheckStatus==false){$('#'+Selector+":not(:disabled)").prop('checked',false);}else{$('#'+Selector+":not(:disabled)").prop('checked',true);}
					
				//Restore
                action="Restore"
                Selector="chk"+slug+action;
                elems=$('.'+Selector);
                CheckStatus=true;
				if(elems.length>0){
					for(j=0;j<elems.length;j++){
						var elem=elems[j];
						var elemid=elem.getAttribute('id');
						var crudvalue=elem.getAttribute('data-crud-value');
						if(crudvalue==1){
							if($('#'+elemid).prop('checked')==false){
								CheckStatus=false;break;
							}
						}
					}
				}else{
					$('#'+Selector).attr('disabled',true);
					$('#'+Selector).attr('data-crud-value',0);
				}
				if(CheckStatus==false){$('#'+Selector+":not(:disabled)").prop('checked',false);}else{$('#'+Selector+":not(:disabled)").prop('checked',true);}
					
				//Show password
                action="ShowPwd"
                Selector="chk"+slug+action;
                elems=$('.'+Selector);
                CheckStatus=true;
				if(elems.length>0){
					for(j=0;j<elems.length;j++){
						var elem=elems[j];
						var elemid=elem.getAttribute('id');
						var crudvalue=elem.getAttribute('data-crud-value');
						if(crudvalue==1){
							if($('#'+elemid).prop('checked')==false){
								CheckStatus=false;break;
							}
						}
					}
				}else{
					$('#'+Selector).attr('disabled',true);
					$('#'+Selector).attr('data-crud-value',0);
				}
				if(CheckStatus==false){$('#'+Selector+":not(:disabled)").prop('checked',false);}else{$('#'+Selector+":not(:disabled)").prop('checked',true);}
					
			}
		}
	}
	const ChangeCheckStatusALL=async()=>{
        var myTable 	= document.getElementById('tblUserRights');
        var totrows		= $('#tblUserRights tbody tr').length;
        for(i=2;i<=totrows;i++){
			//ADD
            var Selector="chkAddAll";
			var elems=$('.'+Selector);
			var CheckStatus=true;
			for(j=0;j<elems.length;j++){
				var elem=elems[j];
				var elemid=elem.getAttribute('id');
				var crudvalue=elem.getAttribute('data-crud-value');
				if(crudvalue==1){
					if($('#'+elemid).prop('checked')==false){
						CheckStatus=false;break;
					}
				}
			}
			if(CheckStatus==false){$('#'+Selector).prop('checked',false);}else{$('#'+Selector).prop('checked',true);}
			//VIEW
            var Selector="chkViewAll";
			var elems=$('.'+Selector);
			var CheckStatus=true;
			for(j=0;j<elems.length;j++){
				var elem=elems[j];
				var elemid=elem.getAttribute('id');
				var crudvalue=elem.getAttribute('data-crud-value');
				if(crudvalue==1){
					if($('#'+elemid).prop('checked')==false){
						CheckStatus=false;break;
					}
				}
			}
			if(CheckStatus==false){$('#'+Selector).prop('checked',false);}else{$('#'+Selector).prop('checked',true);}
			//EDIT
            var Selector="chkEditAll";
			var elems=$('.'+Selector);
			var CheckStatus=true;
			for(j=0;j<elems.length;j++){
				var elem=elems[j];
				var elemid=elem.getAttribute('id');
				var crudvalue=elem.getAttribute('data-crud-value');
				if(crudvalue==1){
					if($('#'+elemid).prop('checked')==false){
						CheckStatus=false;break;
					}
				}
			}
			if(CheckStatus==false){$('#'+Selector).prop('checked',false);}else{$('#'+Selector).prop('checked',true);}
			//DELETE
            var Selector="chkDeleteAll";
			var elems=$('.'+Selector);
			var CheckStatus=true;
			for(j=0;j<elems.length;j++){
				var elem=elems[j];
				var elemid=elem.getAttribute('id');
				var crudvalue=elem.getAttribute('data-crud-value');
				if(crudvalue==1){
					if($('#'+elemid).prop('checked')==false){
						CheckStatus=false;break;
					}
				}
			}
			if(CheckStatus==false){$('#'+Selector).prop('checked',false);}else{$('#'+Selector).prop('checked',true);}
			//COPY
            var Selector="chkCopyAll";
			var elems=$('.'+Selector);
			var CheckStatus=true;
			for(j=0;j<elems.length;j++){
				var elem=elems[j];
				var elemid=elem.getAttribute('id');
				var crudvalue=elem.getAttribute('data-crud-value');
				if(crudvalue==1){
					if($('#'+elemid).prop('checked')==false){
						CheckStatus=false;break;
					}
				}
			}
			if(CheckStatus==false){$('#'+Selector).prop('checked',false);}else{$('#'+Selector).prop('checked',true);}
			//EXCEL
            var Selector="chkExcelAll";
			var elems=$('.'+Selector);
			var CheckStatus=true;
			for(j=0;j<elems.length;j++){
				var elem=elems[j];
				var elemid=elem.getAttribute('id');
				var crudvalue=elem.getAttribute('data-crud-value');
				if(crudvalue==1){
					if($('#'+elemid).prop('checked')==false){
						CheckStatus=false;break;
					}
				}
			}
			if(CheckStatus==false){$('#'+Selector).prop('checked',false);}else{$('#'+Selector).prop('checked',true);}
			//CSV
            var Selector="chkCSVAll";
			var elems=$('.'+Selector);
			var CheckStatus=true;
			for(j=0;j<elems.length;j++){
				var elem=elems[j];
				var elemid=elem.getAttribute('id');
				var crudvalue=elem.getAttribute('data-crud-value');
				if(crudvalue==1){
					if($('#'+elemid).prop('checked')==false){
						CheckStatus=false;break;
					}
				}
			}
			if(CheckStatus==false){$('#'+Selector).prop('checked',false);}else{$('#'+Selector).prop('checked',true);}
			//PRINT
            var Selector="chkPrintAll";
			var elems=$('.'+Selector);
			var CheckStatus=true;
			for(j=0;j<elems.length;j++){
				var elem=elems[j];
				var elemid=elem.getAttribute('id');
				var crudvalue=elem.getAttribute('data-crud-value');
				if(crudvalue==1){
					if($('#'+elemid).prop('checked')==false){
						CheckStatus=false;break;
					}
				}
			}
			if(CheckStatus==false){$('#'+Selector).prop('checked',false);}else{$('#'+Selector).prop('checked',true);}
				
			//PDF
            var Selector="chkPDFAll";
			var elems=$('.'+Selector);
			var CheckStatus=true;
			for(j=0;j<elems.length;j++){
				var elem=elems[j];
				var elemid=elem.getAttribute('id');
				var crudvalue=elem.getAttribute('data-crud-value');
				if(crudvalue==1){
					if($('#'+elemid).prop('checked')==false){
						CheckStatus=false;break;
					}
				}
			}
			if(CheckStatus==false){$('#'+Selector).prop('checked',false);}else{$('#'+Selector).prop('checked',true);}
				
			//Restore
            var Selector="chkRestoreAll";
			var elems=$('.'+Selector);
			var CheckStatus=true;
			for(j=0;j<elems.length;j++){
				var elem=elems[j];
				var elemid=elem.getAttribute('id');
				var crudvalue=elem.getAttribute('data-crud-value');
				if(crudvalue==1){
					if($('#'+elemid).prop('checked')==false){
						CheckStatus=false;break;
					}
				}
			}
			if(CheckStatus==false){$('#'+Selector).prop('checked',false);}else{$('#'+Selector).prop('checked',true);}
				
			//Show Password
            var Selector="chkShowPwdAll";
			var elems=$('.'+Selector);
			var CheckStatus=true;
			for(j=0;j<elems.length;j++){
				var elem=elems[j];
				var elemid=elem.getAttribute('id');
				var crudvalue=elem.getAttribute('data-crud-value');
				if(crudvalue==1){
					if($('#'+elemid).prop('checked')==false){
						CheckStatus=false;break;
					}
				}
			}
			if(CheckStatus==false){$('#'+Selector).prop('checked',false);}else{$('#'+Selector).prop('checked',true);}
				
		}
	}
    const getUserRoleInfo=async()=>{
        if(RoleID!="" && cruds.edit==true ){
            $.ajax({
                type:"post",
                url:RootUrl+"users-and-permissions/user-roles/json/"+RoleID,
                headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
                data:{},
                async:false,
                dataType:"json",
                error:function(e, x, settings, exception){ajaxErrors(e, x, settings, exception);},
                success:function(response){
                    if(response.length>0){
                        response=response[0];
                        $('#txtRoleName').val(response.RoleName);
                        loadUserRights(response.CRUD);

                    }
                }
            });
        }
    }
    const loadUserRights=async(UserRight)=>{
		var UserRoles={};
        $('#tblUserRights tbody tr').each(function(){
            let elem=$(this)[0].cells[0];
			var mid=elem.attributes['data-id'].value;
			var slug=elem.attributes['data-slug'].value;
			var ParentSlug=elem.attributes['data-parent-slug'].value;
			var AddID="chk"+slug+"Add",ViewID="chk"+slug+"View",EditID="chk"+slug+"Edit",DeleteID="chk"+slug+"Delete",CopyID="chk"+slug+"Copy",ExcelID="chk"+slug+"Excel",CSVID="chk"+slug+"CSV",PrintID="chk"+slug+"Print",PDFID="chk"+slug+"PDF",RestoreID="chk"+slug+"Restore",ApprovalID="chk"+slug+"Approval",ShowPwdID="chk"+slug+"ShowPwd";
			if(UserRight[mid]!=undefined){
                if(UserRight[mid]['add']==1){$('#'+AddID).prop('checked',true);}else{$('#'+AddID).prop('checked',false);}
                if(UserRight[mid]['view']==1){$('#'+ViewID).prop('checked',true);}else{$('#'+ViewID).prop('checked',false);}
                if(UserRight[mid]['edit']==1){$('#'+EditID).prop('checked',true);}else{$('#'+EditID).prop('checked',false);}
                if(UserRight[mid]['delete']==1){$('#'+DeleteID).prop('checked',true);}else{$('#'+DeleteID).prop('checked',false);}
                if(UserRight[mid]['copy']==1){$('#'+CopyID).prop('checked',true);}else{$('#'+CopyID).prop('checked',false);}
                if(UserRight[mid]['excel']==1){$('#'+ExcelID).prop('checked',true);}else{$('#'+ExcelID).prop('checked',false);}
                if(UserRight[mid]['csv']==1){$('#'+CSVID).prop('checked',true);}else{$('#'+CSVID).prop('checked',false);}
                if(UserRight[mid]['print']==1){$('#'+PrintID).prop('checked',true);}else{$('#'+PrintID).prop('checked',false);}
                if(UserRight[mid]['pdf']==1){$('#'+PDFID).prop('checked',true);}else{$('#'+PDFID).prop('checked',false);}
                if(UserRight[mid]['restore']==1){$('#'+RestoreID).prop('checked',true);}else{$('#'+RestoreID).prop('checked',false);}
				if(UserRight[mid]['showpwd']==1){$('#'+ShowPwdID).prop('checked',true);}else{$('#'+ShowPwdID).prop('checked',false);}
                EnableDisable(slug,ParentSlug);
            }
        });
        /*
		var myTable 	= document.getElementById('tblUserRights');
        var totrows		= $('#tblUserRights tbody tr').length;
        
        for(i=1;i<=(parseInt(totrows)+1);i++){console.log(myTable.rows[i])
			var elem=myTable.rows[i].cells[0];
			var mid=elem.attributes['data-id'].value;
			var slug=elem.attributes['data-slug'].value;
			var ParentSlug=elem.attributes['data-parent-slug'].value;
			var AddID="chk"+slug+"Add",ViewID="chk"+slug+"View",EditID="chk"+slug+"Edit",DeleteID="chk"+slug+"Delete",CopyID="chk"+slug+"Copy",ExcelID="chk"+slug+"Excel",CSVID="chk"+slug+"CSV",PrintID="chk"+slug+"Print",PDFID="chk"+slug+"PDF",RestoreID="chk"+slug+"Restore",ApprovalID="chk"+slug+"Approval",ShowPwdID="chk"+slug+"ShowPwd";
			if(UserRight[mid]!=undefined){
                if(UserRight[mid]['add']==1){$('#'+AddID).prop('checked',true);}else{$('#'+AddID).prop('checked',false);}
                if(UserRight[mid]['view']==1){$('#'+ViewID).prop('checked',true);}else{$('#'+ViewID).prop('checked',false);}
                if(UserRight[mid]['edit']==1){$('#'+EditID).prop('checked',true);}else{$('#'+EditID).prop('checked',false);}
                if(UserRight[mid]['delete']==1){$('#'+DeleteID).prop('checked',true);}else{$('#'+DeleteID).prop('checked',false);}
                if(UserRight[mid]['copy']==1){$('#'+CopyID).prop('checked',true);}else{$('#'+CopyID).prop('checked',false);}
                if(UserRight[mid]['excel']==1){$('#'+ExcelID).prop('checked',true);}else{$('#'+ExcelID).prop('checked',false);}
                if(UserRight[mid]['csv']==1){$('#'+CSVID).prop('checked',true);}else{$('#'+CSVID).prop('checked',false);}
                if(UserRight[mid]['print']==1){$('#'+PrintID).prop('checked',true);}else{$('#'+PrintID).prop('checked',false);}
                if(UserRight[mid]['pdf']==1){$('#'+PDFID).prop('checked',true);}else{$('#'+PDFID).prop('checked',false);}
                if(UserRight[mid]['restore']==1){$('#'+RestoreID).prop('checked',true);}else{$('#'+RestoreID).prop('checked',false);}
				if(UserRight[mid]['showpwd']==1){$('#'+ShowPwdID).prop('checked',true);}else{$('#'+ShowPwdID).prop('checked',false);}
                EnableDisable(slug,ParentSlug);
            }
		}*/
        MenuGroupStatus();
	}
    const getUserRights=async()=>{
        var UserRoles={};
        $('#tblUserRights tbody tr').each(function(){
            let elem=$(this)[0].cells[0];
            var mid=elem.attributes['data-id'].value;
            var slug=elem.attributes['data-slug'].value;
            var t={'add':0,"view":0,"edit":0,"delete":0,"copy":0,"excel":0,"csv":0,"print":0,"pdf":0,"restore":0,"approval":0,"showpwd":0};
            //Add
            if(($('#chk'+slug+'Add').attr('disabled')==undefined)&&($('#chk'+slug+'Add').attr('data-crud-value')=="1")){
                if($('#chk'+slug+'Add').prop('checked')==true){t['add']=1;}
            }
            //View 
            if((slug=="dashboard")||(slug=="profile")||(slug=="change-password")){
                   t['view']=1;
            }else{
                if((($('#chk'+slug+'View').attr('disabled')==false)||($('#chk'+slug+'View').attr('disabled')==undefined))&&($('#chk'+slug+'View').attr('data-crud-value')=="1")){
                    if($('#chk'+slug+'View').prop('checked')==true){t['view']=1;}
                }
            }
            //Edit
            if((($('#chk'+slug+'Edit').attr('disabled')==false)||($('#chk'+slug+'Edit').attr('disabled')==undefined))&&($('#chk'+slug+'Edit').attr('data-crud-value')=="1")){
                if($('#chk'+slug+'Edit').prop('checked')==true){t['edit']=1;}
            }
            //Delete
            if((($('#chk'+slug+'Delete').attr('disabled')==false)||($('#chk'+slug+'Delete').attr('disabled')==undefined))&&($('#chk'+slug+'Delete').attr('data-crud-value')=="1")){
                if($('#chk'+slug+'Delete').prop('checked')==true){t['delete']=1;}
            }
            //Copy
            if((($('#chk'+slug+'Copy').attr('disabled')==false)||($('#chk'+slug+'Copy').attr('disabled')==undefined))&&($('#chk'+slug+'Copy').attr('data-crud-value')=="1")){
                if($('#chk'+slug+'Copy').prop('checked')==true){t['copy']=1;}
            }
            //Excel
            if((($('#chk'+slug+'Excel').attr('disabled')==false)||($('#chk'+slug+'Excel').attr('disabled')==undefined))&&($('#chk'+slug+'Excel').attr('data-crud-value')=="1")){
                if($('#chk'+slug+'Excel').prop('checked')==true){t['excel']=1;}
            }
            //CSV
            if((($('#chk'+slug+'CSV').attr('disabled')==false)||($('#chk'+slug+'CSV').attr('disabled')==undefined))&&($('#chk'+slug+'CSV').attr('data-crud-value')=="1")){
                if($('#chk'+slug+'CSV').prop('checked')==true){t['csv']=1;}
            }
            //Print
            if((($('#chk'+slug+'Print').attr('disabled')==false)||($('#chk'+slug+'Print').attr('disabled')==undefined))&&($('#chk'+slug+'Print').attr('data-crud-value')=="1")){
                if($('#chk'+slug+'Print').prop('checked')==true){t['print']=1;}
            }
            //PDF
            if((($('#chk'+slug+'PDF').attr('disabled')==false)||($('#chk'+slug+'PDF').attr('disabled')==undefined))&&($('#chk'+slug+'PDF').attr('data-crud-value')=="1")){
                if($('#chk'+slug+'PDF').prop('checked')==true){t['pdf']=1;}
            }
            //PDF
            if((($('#chk'+slug+'Restore').attr('disabled')==false)||($('#chk'+slug+'Restore').attr('disabled')==undefined))&&($('#chk'+slug+'Restore').attr('data-crud-value')=="1")){
                if($('#chk'+slug+'Restore').prop('checked')==true){t['restore']=1;}
            }
            //ShowPwd
            if((($('#chk'+slug+'ShowPwd').attr('disabled')==false)||($('#chk'+slug+'ShowPwd').attr('disabled')==undefined))&&($('#chk'+slug+'ShowPwd').attr('data-crud-value')=="1")){
                if($('#chk'+slug+'ShowPwd').prop('checked')==true){t['showpwd']=1;}
            }
            UserRoles[mid]=t;
        })
        /*
        var myTable 	= document.getElementById('tblUserRights');
        var totrows		= $('#tblUserRights tbody tr').length;
        for(i=0;i<=(parseInt(totrows)+1);i++){
            var elem=myTable.rows[i].cells[0];
            var mid=elem.attributes['data-id'].value;
            var slug=elem.attributes['data-slug'].value;
            var t={'add':0,"view":0,"edit":0,"delete":0,"copy":0,"excel":0,"csv":0,"print":0,"pdf":0,"restore":0,"approval":0,"showpwd":0};
            //Add
            if(($('#chk'+slug+'Add').attr('disabled')==undefined)&&($('#chk'+slug+'Add').attr('data-crud-value')=="1")){
                if($('#chk'+slug+'Add').prop('checked')==true){t['add']=1;}
            }
            //View 
            if((slug=="dashboard")||(slug=="profile")||(slug=="change-password")){
                   t['view']=1;
            }else{
                if((($('#chk'+slug+'View').attr('disabled')==false)||($('#chk'+slug+'View').attr('disabled')==undefined))&&($('#chk'+slug+'View').attr('data-crud-value')=="1")){
                    if($('#chk'+slug+'View').prop('checked')==true){t['view']=1;}
                }
            }
            //Edit
            if((($('#chk'+slug+'Edit').attr('disabled')==false)||($('#chk'+slug+'Edit').attr('disabled')==undefined))&&($('#chk'+slug+'Edit').attr('data-crud-value')=="1")){
                if($('#chk'+slug+'Edit').prop('checked')==true){t['edit']=1;}
            }
            //Delete
            if((($('#chk'+slug+'Delete').attr('disabled')==false)||($('#chk'+slug+'Delete').attr('disabled')==undefined))&&($('#chk'+slug+'Delete').attr('data-crud-value')=="1")){
                if($('#chk'+slug+'Delete').prop('checked')==true){t['delete']=1;}
            }
            //Copy
            if((($('#chk'+slug+'Copy').attr('disabled')==false)||($('#chk'+slug+'Copy').attr('disabled')==undefined))&&($('#chk'+slug+'Copy').attr('data-crud-value')=="1")){
                if($('#chk'+slug+'Copy').prop('checked')==true){t['copy']=1;}
            }
            //Excel
            if((($('#chk'+slug+'Excel').attr('disabled')==false)||($('#chk'+slug+'Excel').attr('disabled')==undefined))&&($('#chk'+slug+'Excel').attr('data-crud-value')=="1")){
                if($('#chk'+slug+'Excel').prop('checked')==true){t['excel']=1;}
            }
            //CSV
            if((($('#chk'+slug+'CSV').attr('disabled')==false)||($('#chk'+slug+'CSV').attr('disabled')==undefined))&&($('#chk'+slug+'CSV').attr('data-crud-value')=="1")){
                if($('#chk'+slug+'CSV').prop('checked')==true){t['csv']=1;}
            }
            //Print
            if((($('#chk'+slug+'Print').attr('disabled')==false)||($('#chk'+slug+'Print').attr('disabled')==undefined))&&($('#chk'+slug+'Print').attr('data-crud-value')=="1")){
                if($('#chk'+slug+'Print').prop('checked')==true){t['print']=1;}
            }
            //PDF
            if((($('#chk'+slug+'PDF').attr('disabled')==false)||($('#chk'+slug+'PDF').attr('disabled')==undefined))&&($('#chk'+slug+'PDF').attr('data-crud-value')=="1")){
                if($('#chk'+slug+'PDF').prop('checked')==true){t['pdf']=1;}
            }
            //PDF
            if((($('#chk'+slug+'Restore').attr('disabled')==false)||($('#chk'+slug+'Restore').attr('disabled')==undefined))&&($('#chk'+slug+'Restore').attr('data-crud-value')=="1")){
                if($('#chk'+slug+'Restore').prop('checked')==true){t['restore']=1;}
            }
            //ShowPwd
            if((($('#chk'+slug+'ShowPwd').attr('disabled')==false)||($('#chk'+slug+'ShowPwd').attr('disabled')==undefined))&&($('#chk'+slug+'ShowPwd').attr('data-crud-value')=="1")){
                if($('#chk'+slug+'ShowPwd').prop('checked')==true){t['showpwd']=1;}
            }
            UserRoles[mid]=t;
        }*/
        return UserRoles;
    }
    const FormValidation=async(data)=>{
        var status=true;
        if(data['RoleName']==""){
            $('#txtRoleName-err').html("The User Role  is required");status=false;
        }
        if(status==false){
            document.body.scrollTop = 0; // For Safari
            document.documentElement.scrollTop = 0; // For Chrome, Firefox, IE and Opera
        }
        return status;
    }
    const saveUserRoles=async(isEdit=false)=>{
        let UserRights=await getUserRights();
        let FormData={};
            FormData['RoleName']= $('#txtRoleName').val();
            FormData['CRUD']= JSON.stringify(UserRights);
        let Status=await FormValidation(FormData);
        if(Status==true){
            if(isEdit){
                var submiturl=RootUrl+"users-and-permissions/user-roles/edit/"+RoleID;
            }else{
                var submiturl=RootUrl+"users-and-permissions/user-roles/create";
            }
            swal({
                title: "Are you sure?",
                text: "You want Save  this User Role!",
                type: "warning",
                showCancelButton: true,
                confirmButtonClass: "btn-outline-success",
                confirmButtonText: "Yes, Save it!",
                closeOnConfirm: false
            },
            function(){
                swal.close();
                btnLoading($('#btnSubmit'));
                $.ajax({
                    type:"post",
                    url:submiturl,
                    headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
                    data:FormData,
                    error:function(e, x, settings, exception){ajaxErrors(e, x, settings, exception);},
                    complete: function(e, x, settings, exception){btnReset($('#btnSubmit'));},
                    success:function(response){
                        if(response.status==true){
                            toastr.success(response.message, "Success", {
                                positionClass: "toast-top-right",
                                containerId: "toast-top-right",
                                showMethod: "slideDown",
                                hideMethod: "slideUp",
                                progressBar: !0
                            });
                            if(response.RoleID!=undefined){
                                RoleID=response.RoleID;
                            }
                            if( $('#btnCloseModal1').length>0){
                                $('#btnCloseModal1').trigger('click');
                            }
                            if( $('#btnReloadUserRoles').length>0){
                                $('#btnReloadUserRoles').trigger('click');
                            }
                        }else{
                            if(response['errors']!=undefined){
                                $('.errors').html('');
                                toastr.error(response.message, "Failed", {
                                    positionClass: "toast-top-right",
                                    containerId: "toast-top-right",
                                    showMethod: "slideDown",
                                    hideMethod: "slideUp",
                                    progressBar: !0
                                });
                                $.each( response['errors'], function( KeyName, KeyValue ) {
                                    var key=KeyName;
                                    if(key=="RoleName"){$('#txtRoleName-err').html(KeyValue[0]);}
                                    
                                });
                                document.body.scrollTop = 0; // For Safari
                                document.documentElement.scrollTop = 0; // For Chrome, Firefox, IE and Opera
                            }
                        }
                    }
                });
            });
        
        }
    }
    const getUserRoles=async(elem)=>{
        $('#'+elem).select2('destroy');
        $('#'+elem+' option').remove();
        $('#'+elem).append('<option value="" selected>Select a Role</option>');
        $.ajax({
            type:"post",
            url:RootUrl+"users-and-permissions/users/get/user-roles",
            headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
            dataType:"json",
            async:true,
            error:function(e, x, settings, exception){ajaxErrors(e, x, settings, exception);},
            complete: function(e, x, settings, exception){},
            success:function(response){
                for(let Item of response){
                    let selected="";
                    if(Item.RoleID==$('#'+elem).attr('data-selected')){selected="selected";}
                    $('#'+elem).append('<option '+selected+' value="'+Item.RoleID+'">'+Item.RoleName+' </option>');
                }
            }
        });
        $('#'+elem).select2();
    }
    $(document).on('click','#btnCustomizeRole',function(){
        let id=$(this).parent().attr('for');
        RoleID=$('#'+id).val();
        cruds.add=parseInt($(this).attr('data-crud-add'))==1?true:false;
        cruds.edit=parseInt($(this).attr('data-crud-edit'))==1?true:false;
        console.log(cruds)
        bootbox.dialog({
            title: 'User Roles',
            closeButton: true,
            message: getUserRightsTable(),
            size:'large',
            buttons: {
            }
        });
        setTimeout(() => {
            getMenus();
        }, 400);
    });
    $(document).on('click','#chkAddAll',function(){
        if($('#chkAddAll').prop('checked')==true){
            $('.chkAddAll:not(:disabled)').prop('checked', true);
        }else{
            $('.chkAddAll:not(:disabled)').prop('checked', false);
        }
    });
    $(document).on('click','#chkViewAll',function(){
        if($('#chkViewAll').prop('checked')==true){
            $('.chkViewAll:not(:disabled)').prop('checked', true);
        }else{
            $('.chkViewAll:not(:disabled)').prop('checked', false);
        }
    });
    $(document).on('click','#chkEditAll',function(){
        if($('#chkEditAll').prop('checked')==true){
            $('.chkEditAll:not(:disabled)').prop('checked', true);
        }else{
            $('.chkEditAll:not(:disabled)').prop('checked', false);
        }
    });
    $(document).on('click','#chkDeleteAll',function(){
        if($('#chkDeleteAll').prop('checked')==true){
            $('.chkDeleteAll:not(:disabled)').prop('checked', true);
        }else{
            $('.chkDeleteAll:not(:disabled)').prop('checked', false);
        }
    });
    $(document).on('click','#chkCopyAll',function(){
        if($('#chkCopyAll').prop('checked')==true){
            $('.chkCopyAll:not(:disabled)').prop('checked', true);
        }else{
            $('.chkCopyAll:not(:disabled)').prop('checked', false);
        }
    });
    $(document).on('click','#chkExcelAll',function(){
        if($('#chkExcelAll').prop('checked')==true){
            $('.chkExcelAll:not(:disabled)').prop('checked', true);
        }else{
            $('.chkExcelAll:not(:disabled)').prop('checked', true);
        }
    });
    $(document).on('click','#chkCSVAll',function(){
        if($('#chkCSVAll').prop('checked')==true){
            $('.chkCSVAll:not(:disabled)').prop('checked', true);
        }else{
            $('.chkCSVAll:not(:disabled)').prop('checked', true);
        }
    });
    $(document).on('click','#chkPrintAll',function(){
        if($('#chkPrintAll').prop('checked')==true){
            $('.chkPrintAll:not(:disabled)').prop('checked', true);
        }else{
            $('.chkPrintAll:not(:disabled)').prop('checked', true);
        }
    });
    $(document).on('click','#chkPDFAll',function(){
        if($('#chkPDFAll').prop('checked')==true){
            $('.chkPDFAll:not(:disabled)').prop('checked', true);
        }else{
            $('.chkPDFAll:not(:disabled)').prop('checked', true);
        }
    });
    $(document).on('click','#chkRestoreAll',function(){
        if($('#chkRestoreAll').prop('checked')==true){
            $('.chkRestoreAll:not(:disabled)').prop('checked', true);
        }else{
            $('.chkRestoreAll:not(:disabled)').prop('checked', true);
        }
    });
    $(document).on('click','#chkShowPwdAll',function(){
        if($('#chkShowPwdAll').prop('checked')==true){
            $('.chkShowPwdAll:not(:disabled)').prop('checked', true);
        }else{
            $('.chkShowPwdAll:not(:disabled)').prop('checked', true);
        }
    });
    $(document).on('click','.chkClick',function(){
		var parentslug=$(this).attr('data-parent-slug');
		var slug=$(this).attr('data-slug');
		var crud=$(this).attr('data-crud');
		var ClassName="chk"+slug+crud;
		if($(this).prop('checked')==false){
			$('.'+ClassName+ ':not(:disabled)').prop('checked', false);
		}else{
			$('.'+ClassName+ ':not(:disabled)').prop('checked', true);
		}
		if(crud=="View"){
			var Elems=$('input[data-parent-slug='+slug+'][data-crud="View"]' );
			for(var i=0;i<Elems.length;i++){
				var crud1=Elems[i].getAttribute('data-crud');
				var slug1=Elems[i].getAttribute('data-slug');
				var parentslug1=Elems[i].getAttribute('data-parent-slug');
				EnableDisable(slug1,parentslug1);
			}
		}
		MenuGroupStatus();
		ChangeCheckStatusALL();
	});
	$(document).on('click','.MenuClick',function(){
		var slug=$(this).attr('data-slug');
		var crud=$(this).attr('data-crud');
		var parentslug=$(this).attr('data-parent-slug');
		if(crud=="View"){EnableDisable(slug,parentslug);}
		MenuGroupStatus();
		ChangeCheckStatusALL();
	});
    $(document).on('click','#btnCreateNewRole',function(){
        if(cruds.add){
            saveUserRoles();
        }
    })
    $(document).on('click','#btnUpdateRole',function(){
        if(cruds.edit && RoleID !=""){
            saveUserRoles(true);
        }
    })
    $('#btnReloadUserRoles').click(function(){
        let id=$(this).parent().attr('for');
        $('#'+id).attr('data-selected',RoleID);
        getUserRoles(id);
    });
    $(document).on('click','#btnCloseModal1',function(){
        bootbox.hideAll();
    });
});