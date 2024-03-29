<?php
if(!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true)die();
if(isPost()){
    //Подготавливаем переменные
    $arParams["EMAIL_TO"] = trim($arParams["EMAIL_TO"]);
    if($arParams["EMAIL_TO"] == '')
        $arParams["EMAIL_TO"] = COption::GetOptionString("main", "email_from");//
    if($arParams["EVENT_NAME"] == '')
        $arParams["EVENT_NAME"] = "FEEDBACK_FORM";
    //Обработка POST-данных
    $formFields = ['user_name','user_phone', 'user_email', 'message'];
    $arResult ['FORM'] = [ ];
    foreach ($formFields as $key ) {
        $arResult ['FORM'] [ $key] = htmlspecialcharsBx (trim ( $_POST [ $key ] ) );
    }
    //Массив для передачи в поля инфоблока
    CModule::IncludeModule('iblock');
    
    $el = new CIBlockElement;
    $iblock_type = $arParams["IBLOCK_TYPE"];
    $iblock_id = $arParams["IBLOCK_ID"];
    $PROP = array();
    $properties = CIBlockProperty::GetList(Array("sort"=>"asc", "id"=>"asc"), Array("ACTIVE"=>"Y", "IBLOCK_ID"=>$iblock_id));
    $PROP['NAME']=$arResult ['FORM']['user_name'];
    $PROP['PHONE']=$arResult ['FORM']['user_phone'];
    $PROP['EMAIL']=$arResult ['FORM']['user_email'];
    $PROP['MESSAGE']=$arResult ['FORM']['message'];
    $fields = array(
    "IBLOCK_ID" => $iblock_id, 
    "PROPERTY_VALUES" => $PROP, // Передаем массив значении для свойств
    "NAME" => date("d.m.Y H:i:s"),
    );
    if($ID = $el->Add($fields)){//Отправляем значения в инфоблок, 
    //если успешно, переходим к отправке почтового шаблона
    
        $rsSites = CSite::GetByID(SITE_ID);//Получаем SERVER_NAME
        $arSite = $rsSites->Fetch();
        
        $viewLink = "http://".$arSite[SERVER_NAME]."/bitrix/admin/iblock_element_edit.php?IBLOCK_ID=".$iblock_id."&type=".$iblock_type."&ID=".$ID."&lang=ru&find_section_section=0&WF=Y";//Ссылка на детальный просмотр - редактирование, без только без использования разделов
        $arFields = Array(
            "AUTHOR" => $arResult ['FORM']['user_name'],
            "PHONE" => $arResult ['FORM']['user_phone'],
            "AUTHOR_EMAIL" => $arResult ['FORM']['user_email'],
            "EMAIL_TO" => $arParams["EMAIL_TO"],
            "TEXT" => $arResult ['FORM']['message'],
            "VIEW_LINK" =>$viewLink ,
        );
        
        if(CEvent::Send($arParams["EVENT_NAME"], SITE_ID, $arFields))//отправка в почтовый шаблон
            echo "success";
    }
}else{
    $this->IncludeComponentTemplate();
}