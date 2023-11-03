<?php return array (
  'ilias\\file\\icon\\iliconuploadhandlergui' => 
  array (
    'cid' => '9',
    'class_name' => 'ILIAS\\File\\Icon\\ilIconUploadHandlerGUI',
    'class_path' => './components/ILIAS/File/classes/Icons/class.ilIconUploadHandlerGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilias\\file\\icon\\ilobjfileiconsoverviewgui',
    ),
  ),
  'ilias\\file\\icon\\ilobjfileiconsoverviewgui' => 
  array (
    'cid' => 'a',
    'class_name' => 'ILIAS\\File\\Icon\\ilObjFileIconsOverviewGUI',
    'class_path' => './components/ILIAS/File/classes/Icons/class.ilObjFileIconsOverviewGUI.php',
    'children' => 
    array (
      0 => 'ilias\\file\\icon\\iliconuploadhandlergui',
    ),
    'parents' => 
    array (
      0 => 'ilobjfileaccesssettingsgui',
    ),
  ),
  'mcstimagegallerygui' => 
  array (
    'cid' => '11',
    'class_name' => 'McstImageGalleryGUI',
    'class_path' => './components/ILIAS/MediaCast/Presentation/class.McstImageGalleryGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjmediacastgui',
    ),
  ),
  'mcstpodcastgui' => 
  array (
    'cid' => '12',
    'class_name' => 'McstPodcastGUI',
    'class_path' => './components/ILIAS/MediaCast/Presentation/class.McstPodcastGUI.php',
    'children' => 
    array (
      0 => 'ilmediaobjectsplayerwrappergui',
    ),
    'parents' => 
    array (
      0 => 'ilobjmediacastgui',
    ),
  ),
  'skilltreeadmingui' => 
  array (
    'cid' => '13',
    'class_name' => 'SkillTreeAdminGUI',
    'class_path' => './Services/Skill/Tree/class.SkillTreeAdminGUI.php',
    'children' => 
    array (
      0 => 'ilobjskilltreegui',
    ),
    'parents' => 
    array (
      0 => 'ilobjskillmanagementgui',
    ),
  ),
  'surveymatrixquestiongui' => 
  array (
    'cid' => '15',
    'class_name' => 'SurveyMatrixQuestionGUI',
    'class_path' => './components/ILIAS/SurveyQuestionPool/Questions/class.SurveyMatrixQuestionGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjsurveyquestionpoolgui',
      1 => 'ilsurveyeditorgui',
    ),
  ),
  'surveymetricquestiongui' => 
  array (
    'cid' => '16',
    'class_name' => 'SurveyMetricQuestionGUI',
    'class_path' => './components/ILIAS/SurveyQuestionPool/Questions/class.SurveyMetricQuestionGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjsurveyquestionpoolgui',
      1 => 'ilsurveyeditorgui',
    ),
  ),
  'surveymultiplechoicequestiongui' => 
  array (
    'cid' => '17',
    'class_name' => 'SurveyMultipleChoiceQuestionGUI',
    'class_path' => './components/ILIAS/SurveyQuestionPool/Questions/class.SurveyMultipleChoiceQuestionGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjsurveyquestionpoolgui',
      1 => 'ilsurveyeditorgui',
    ),
  ),
  'surveysinglechoicequestiongui' => 
  array (
    'cid' => '19',
    'class_name' => 'SurveySingleChoiceQuestionGUI',
    'class_path' => './components/ILIAS/SurveyQuestionPool/Questions/class.SurveySingleChoiceQuestionGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjsurveyquestionpoolgui',
      1 => 'ilsurveyeditorgui',
    ),
  ),
  'surveytextquestiongui' => 
  array (
    'cid' => '1a',
    'class_name' => 'SurveyTextQuestionGUI',
    'class_path' => './components/ILIAS/SurveyQuestionPool/Questions/class.SurveyTextQuestionGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjsurveyquestionpoolgui',
      1 => 'ilsurveyeditorgui',
    ),
  ),
  'assclozetestgui' => 
  array (
    'cid' => '1b',
    'class_name' => 'assClozeTestGUI',
    'class_path' => './components/ILIAS/TestQuestionPool/classes/class.assClozeTestGUI.php',
    'children' => 
    array (
      0 => 'ilformpropertydispatchgui',
      1 => 'iltestexpresspageobjectgui',
    ),
    'parents' => 
    array (
      0 => 'ilobjquestionpoolgui',
      1 => 'ilobjtestgui',
      2 => 'ilquestioneditgui',
      3 => 'iltestexpresspageobjectgui',
    ),
  ),
  'asserrortextgui' => 
  array (
    'cid' => '1c',
    'class_name' => 'assErrorTextGUI',
    'class_path' => './components/ILIAS/TestQuestionPool/classes/class.assErrorTextGUI.php',
    'children' => 
    array (
      0 => 'ilformpropertydispatchgui',
    ),
    'parents' => 
    array (
      0 => 'ilobjquestionpoolgui',
      1 => 'ilobjtestgui',
      2 => 'ilquestioneditgui',
    ),
  ),
  'assfileuploadgui' => 
  array (
    'cid' => '1e',
    'class_name' => 'assFileUploadGUI',
    'class_path' => './components/ILIAS/TestQuestionPool/classes/class.assFileUploadGUI.php',
    'children' => 
    array (
      0 => 'ilformpropertydispatchgui',
    ),
    'parents' => 
    array (
      0 => 'ilobjquestionpoolgui',
      1 => 'ilobjtestgui',
    ),
  ),
  'assformulaquestiongui' => 
  array (
    'cid' => '1f',
    'class_name' => 'assFormulaQuestionGUI',
    'class_path' => './components/ILIAS/TestQuestionPool/classes/class.assFormulaQuestionGUI.php',
    'children' => 
    array (
      0 => 'ilformpropertydispatchgui',
      1 => 'iltestexpresspageobjectgui',
    ),
    'parents' => 
    array (
      0 => 'ilobjquestionpoolgui',
      1 => 'ilobjtestgui',
      2 => 'ilquestioneditgui',
      3 => 'iltestexpresspageobjectgui',
    ),
  ),
  'assimagemapquestiongui' => 
  array (
    'cid' => '1g',
    'class_name' => 'assImagemapQuestionGUI',
    'class_path' => './components/ILIAS/TestQuestionPool/classes/class.assImagemapQuestionGUI.php',
    'children' => 
    array (
      0 => 'ilpropertyformgui',
      1 => 'ilformpropertydispatchgui',
      2 => 'iltestexpresspageobjectgui',
    ),
    'parents' => 
    array (
      0 => 'ilobjquestionpoolgui',
      1 => 'ilobjtestgui',
      2 => 'ilquestioneditgui',
      3 => 'iltestexpresspageobjectgui',
    ),
  ),
  'asskprimchoicegui' => 
  array (
    'cid' => '1h',
    'class_name' => 'assKprimChoiceGUI',
    'class_path' => './components/ILIAS/TestQuestionPool/classes/class.assKprimChoiceGUI.php',
    'children' => 
    array (
      0 => 'ilpropertyformgui',
      1 => 'ilformpropertydispatchgui',
    ),
    'parents' => 
    array (
      0 => 'ilobjquestionpoolgui',
      1 => 'ilobjtestgui',
      2 => 'ilquestioneditgui',
    ),
  ),
  'asslongmenugui' => 
  array (
    'cid' => '1i',
    'class_name' => 'assLongMenuGUI',
    'class_path' => './components/ILIAS/TestQuestionPool/classes/class.assLongMenuGUI.php',
    'children' => 
    array (
      0 => 'ilpropertyformgui',
      1 => 'ilformpropertydispatchgui',
    ),
    'parents' => 
    array (
      0 => 'ilobjquestionpoolgui',
      1 => 'ilobjtestgui',
      2 => 'ilquestioneditgui',
    ),
  ),
  'assmatchingquestiongui' => 
  array (
    'cid' => '1j',
    'class_name' => 'assMatchingQuestionGUI',
    'class_path' => './components/ILIAS/TestQuestionPool/classes/class.assMatchingQuestionGUI.php',
    'children' => 
    array (
      0 => 'ilformpropertydispatchgui',
      1 => 'iltestexpresspageobjectgui',
    ),
    'parents' => 
    array (
      0 => 'ilobjquestionpoolgui',
      1 => 'ilobjtestgui',
      2 => 'ilquestioneditgui',
      3 => 'iltestexpresspageobjectgui',
    ),
  ),
  'assmultiplechoicegui' => 
  array (
    'cid' => '1k',
    'class_name' => 'assMultipleChoiceGUI',
    'class_path' => './components/ILIAS/TestQuestionPool/classes/class.assMultipleChoiceGUI.php',
    'children' => 
    array (
      0 => 'ilformpropertydispatchgui',
      1 => 'iltestexpresspageobjectgui',
    ),
    'parents' => 
    array (
      0 => 'ilobjquestionpoolgui',
      1 => 'ilobjtestgui',
      2 => 'ilquestioneditgui',
      3 => 'iltestexpresspageobjectgui',
    ),
  ),
  'assnumericgui' => 
  array (
    'cid' => '1l',
    'class_name' => 'assNumericGUI',
    'class_path' => './components/ILIAS/TestQuestionPool/classes/class.assNumericGUI.php',
    'children' => 
    array (
      0 => 'ilformpropertydispatchgui',
      1 => 'iltestexpresspageobjectgui',
    ),
    'parents' => 
    array (
      0 => 'ilobjquestionpoolgui',
      1 => 'ilobjtestgui',
      2 => 'ilquestioneditgui',
      3 => 'iltestexpresspageobjectgui',
    ),
  ),
  'assorderinghorizontalgui' => 
  array (
    'cid' => '1m',
    'class_name' => 'assOrderingHorizontalGUI',
    'class_path' => './components/ILIAS/TestQuestionPool/classes/class.assOrderingHorizontalGUI.php',
    'children' => 
    array (
      0 => 'ilpropertyformgui',
      1 => 'ilformpropertydispatchgui',
    ),
    'parents' => 
    array (
      0 => 'ilobjquestionpoolgui',
      1 => 'ilobjtestgui',
      2 => 'ilquestioneditgui',
    ),
  ),
  'assorderingquestiongui' => 
  array (
    'cid' => '1n',
    'class_name' => 'assOrderingQuestionGUI',
    'class_path' => './components/ILIAS/TestQuestionPool/classes/class.assOrderingQuestionGUI.php',
    'children' => 
    array (
      0 => 'ilformpropertydispatchgui',
      1 => 'iltestexpresspageobjectgui',
    ),
    'parents' => 
    array (
      0 => 'ilobjquestionpoolgui',
      1 => 'ilobjtestgui',
      2 => 'ilquestioneditgui',
      3 => 'iltestexpresspageobjectgui',
    ),
  ),
  'asssinglechoicegui' => 
  array (
    'cid' => '1p',
    'class_name' => 'assSingleChoiceGUI',
    'class_path' => './components/ILIAS/TestQuestionPool/classes/class.assSingleChoiceGUI.php',
    'children' => 
    array (
      0 => 'ilformpropertydispatchgui',
      1 => 'iltestexpresspageobjectgui',
    ),
    'parents' => 
    array (
      0 => 'ilobjquestionpoolgui',
      1 => 'ilobjtestgui',
      2 => 'ilquestioneditgui',
      3 => 'iltestexpresspageobjectgui',
    ),
  ),
  'asstextquestiongui' => 
  array (
    'cid' => '1q',
    'class_name' => 'assTextQuestionGUI',
    'class_path' => './components/ILIAS/TestQuestionPool/classes/class.assTextQuestionGUI.php',
    'children' => 
    array (
      0 => 'ilformpropertydispatchgui',
      1 => 'iltestexpresspageobjectgui',
    ),
    'parents' => 
    array (
      0 => 'ilobjquestionpoolgui',
      1 => 'ilobjtestgui',
      2 => 'ilquestioneditgui',
      3 => 'iltestexpresspageobjectgui',
    ),
  ),
  'asstextsubsetgui' => 
  array (
    'cid' => '1r',
    'class_name' => 'assTextSubsetGUI',
    'class_path' => './components/ILIAS/TestQuestionPool/classes/class.assTextSubsetGUI.php',
    'children' => 
    array (
      0 => 'ilformpropertydispatchgui',
      1 => 'iltestexpresspageobjectgui',
    ),
    'parents' => 
    array (
      0 => 'ilobjquestionpoolgui',
      1 => 'ilobjtestgui',
      2 => 'ilquestioneditgui',
      3 => 'iltestexpresspageobjectgui',
    ),
  ),
  'iladnnotificationgui' => 
  array (
    'cid' => '1w',
    'class_name' => 'ilADNNotificationGUI',
    'class_path' => './Services/AdministrativeNotification/classes/class.ilADNNotificationGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjadministrativenotificationgui',
      1 => 'ilobjadministrativenotificationgui',
    ),
  ),
  'ilaccessibilitycontrolconceptgui' => 
  array (
    'cid' => '22',
    'class_name' => 'ilAccessibilityControlConceptGUI',
    'class_path' => './Services/Accessibility/classes/class.ilAccessibilityControlConceptGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilstartupgui',
    ),
  ),
  'ilaccessibilitydocumentgui' => 
  array (
    'cid' => '25',
    'class_name' => 'ilAccessibilityDocumentGUI',
    'class_path' => './Services/Accessibility/classes/Document/class.ilAccessibilityDocumentGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjaccessibilitysettingsgui',
    ),
  ),
  'ilaccessibilitysupportcontactsgui' => 
  array (
    'cid' => '27',
    'class_name' => 'ilAccessibilitySupportContactsGUI',
    'class_path' => './components/ILIAS/SystemFolder/classes/class.ilAccessibilitySupportContactsGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
    ),
  ),
  'ilaccordionpropertiesstoragegui' => 
  array (
    'cid' => '2b',
    'class_name' => 'ilAccordionPropertiesStorageGUI',
    'class_path' => './Services/Accordion/classes/class.ilAccordionPropertiesStorageGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
    ),
  ),
  'ilaccountregistrationgui' => 
  array (
    'cid' => '2c',
    'class_name' => 'ilAccountRegistrationGUI',
    'class_path' => './Services/Registration/classes/class.ilAccountRegistrationGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilstartupgui',
    ),
  ),
  'ilachievementsgui' => 
  array (
    'cid' => '2d',
    'class_name' => 'ilAchievementsGUI',
    'class_path' => './Services/Dashboard/Achievements/classes/class.ilAchievementsGUI.php',
    'children' => 
    array (
      0 => 'illearningprogressgui',
      1 => 'ilpersonalskillsgui',
      2 => 'ilbadgeprofilegui',
      3 => 'illearninghistorygui',
      4 => 'ilusercertificategui',
    ),
    'parents' => 
    array (
      0 => 'ildashboardgui',
    ),
  ),
  'iladministrationgui' => 
  array (
    'cid' => '2h',
    'class_name' => 'ilAdministrationGUI',
    'class_path' => './Services/Administration/classes/class.ilAdministrationGUI.php',
    'children' => 
    array (
      0 => 'ilobjgroupgui',
      1 => 'ilobjfoldergui',
      2 => 'ilobjfilegui',
      3 => 'ilobjcoursegui',
      4 => 'ilcourseobjectivesgui',
      5 => 'ilobjsahslearningmodulegui',
      6 => 'ilobjchatroomgui',
      7 => 'ilobjforumgui',
      8 => 'ilobjlearningmodulegui',
      9 => 'ilobjglossarygui',
      10 => 'ilobjquestionpoolgui',
      11 => 'ilobjsurveyquestionpoolgui',
      12 => 'ilobjtestgui',
      13 => 'ilobjsurveygui',
      14 => 'ilobjexercisegui',
      15 => 'ilobjmediapoolgui',
      16 => 'ilobjfilebasedlmgui',
      17 => 'ilobjcategorygui',
      18 => 'ilobjusergui',
      19 => 'ilobjrolegui',
      20 => 'ilobjuserfoldergui',
      21 => 'ilobjlinkresourcegui',
      22 => 'ilobjroletemplategui',
      23 => 'ilobjrootfoldergui',
      24 => 'ilobjsessiongui',
      25 => 'ilobjportfoliotemplategui',
      26 => 'ilobjsystemfoldergui',
      27 => 'ilobjrolefoldergui',
      28 => 'ilobjauthsettingsgui',
      29 => 'ilobjlanguagefoldergui',
      30 => 'ilobjmailgui',
      31 => 'ilobjobjectfoldergui',
      32 => 'ilobjrecoveryfoldergui',
      33 => 'ilobjsearchsettingsgui',
      34 => 'ilobjstylesettingsgui',
      35 => 'ilobjassessmentfoldergui',
      36 => 'ilobjexternaltoolssettingsgui',
      37 => 'ilobjusertrackinggui',
      38 => 'ilobjadvancededitinggui',
      39 => 'ilobjprivacysecuritygui',
      40 => 'ilobjnewssettingsgui',
      41 => 'ilobjmediacastgui',
      42 => 'ilobjlanguageextgui',
      43 => 'ilobjmdsettingsgui',
      44 => 'ilobjcomponentsettingsgui',
      45 => 'ilobjcalendarsettingsgui',
      46 => 'ilobjsurveyadministrationgui',
      47 => 'ilobjcategoryreferencegui',
      48 => 'ilobjcoursereferencegui',
      49 => 'ilobjremotecoursegui',
      50 => 'ilobjgroupreferencegui',
      51 => 'ilobjforumadministrationgui',
      52 => 'ilobjbloggui',
      53 => 'ilobjpollgui',
      54 => 'ilobjdatacollectiongui',
      55 => 'ilobjremotecategorygui',
      56 => 'ilobjremotewikigui',
      57 => 'ilobjremotelearningmodulegui',
      58 => 'ilobjremoteglossarygui',
      59 => 'ilobjremotefilegui',
      60 => 'ilobjremotegroupgui',
      61 => 'ilobjecssettingsgui',
      62 => 'ilobjcloudgui',
      63 => 'ilobjrepositorysettingsgui',
      64 => 'ilobjwebresourceadministrationgui',
      65 => 'ilobjcourseadministrationgui',
      66 => 'ilobjgroupadministrationgui',
      67 => 'ilobjexerciseadministrationgui',
      68 => 'ilobjtaxonomyadministrationgui',
      69 => 'ilobjloggingsettingsgui',
      70 => 'ilobjbibliographicadmingui',
      71 => 'ilobjbibliographicgui',
      72 => 'ilobjstudyprogrammeadmingui',
      73 => 'ilobjstudyprogrammegui',
      74 => 'ilobjbadgeadministrationgui',
      75 => 'ilmemberexportsettingsgui',
      76 => 'ilobjfileaccesssettingsgui',
      77 => 'ilpermissiongui',
      78 => 'ilobjremotetestgui',
      79 => 'ilpropertyformgui',
      80 => 'ilobjcmixapiadministrationgui',
      81 => 'ilobjcmixapigui',
      82 => 'ilobjlticonsumergui',
      83 => 'ilobjlearningsequenceadmingui',
      84 => 'ilobjcontentpageadministrationgui',
      85 => 'ilobjindividualassessmentgui',
      86 => 'ilcronmanagergui',
      87 => 'ilobjaccessibilitysettingsgui',
      88 => 'ilobjadministrativenotificationgui',
      89 => 'ilobjawarenessadministrationgui',
      90 => 'ilobjblogadministrationgui',
      91 => 'ilobjbookingpoolgui',
      92 => 'ilobjcertificatesettingsgui',
      93 => 'ilobjchatroomadmingui',
      94 => 'ilobjcommentssettingsgui',
      95 => 'ilobjcontactadministrationgui',
      96 => 'ilobjcontentpagegui',
      97 => 'ilobjdashboardsettingsgui',
      98 => 'ilobjdataprotectiongui',
      99 => 'ilobjemployeetalkseriesgui',
      100 => 'ilobjfileservicesgui',
      101 => 'ilobjhelpsettingsgui',
      102 => 'ilobjitemgroupgui',
      103 => 'ilobjltiadministrationgui',
      104 => 'ilobjlearninghistorysettingsgui',
      105 => 'ilobjlearningresourcessettingsgui',
      106 => 'ilobjlearningsequencegui',
      107 => 'ilobjlegaldocumentsgui',
      108 => 'ilobjlegalnoticegui',
      109 => 'ilobjmainmenugui',
      110 => 'ilobjmediacastsettingsgui',
      111 => 'ilobjmediaobjectssettingsgui',
      112 => 'ilobjnotessettingsgui',
      113 => 'ilobjnotificationadmingui',
      114 => 'ilobjobjecttemplateadministrationgui',
      115 => 'ilobjorgunitgui',
      116 => 'ilobjpersonalworkspacesettingsgui',
      117 => 'ilobjportfolioadministrationgui',
      118 => 'ilobjskillmanagementgui',
      119 => 'ilobjsystemcheckgui',
      120 => 'ilobjtaggingsettingsgui',
      121 => 'ilobjtalktemplateadministrationgui',
      122 => 'ilobjtalktemplategui',
      123 => 'ilobjtermsofservicegui',
      124 => 'ilobjwebdavgui',
      125 => 'ilobjwikigui',
      126 => 'ilobjwikisettingsgui',
    ),
    'parents' => 
    array (
    ),
  ),
  'iladvancedmdrecordtranslationgui' => 
  array (
    'cid' => '2p',
    'class_name' => 'ilAdvancedMDRecordTranslationGUI',
    'class_path' => './Services/AdvancedMetaData/classes/Translation/class.ilAdvancedMDRecordTranslationGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'iladvancedmdsettingsgui',
    ),
  ),
  'iladvancedmdsettingsgui' => 
  array (
    'cid' => '2q',
    'class_name' => 'ilAdvancedMDSettingsGUI',
    'class_path' => './Services/AdvancedMetaData/classes/class.ilAdvancedMDSettingsGUI.php',
    'children' => 
    array (
      0 => 'ilpropertyformgui',
      1 => 'iladvancedmdrecordtranslationgui',
    ),
    'parents' => 
    array (
      0 => 'ilobjmdsettingsgui',
      1 => 'ilobjectmetadatagui',
    ),
  ),
  'iladvancedsearchgui' => 
  array (
    'cid' => '2s',
    'class_name' => 'ilAdvancedSearchGUI',
    'class_path' => './Services/Search/classes/class.ilAdvancedSearchGUI.php',
    'children' => 
    array (
      0 => 'ilobjectgui',
      1 => 'ilcontainergui',
      2 => 'ilobjcategorygui',
      3 => 'ilobjcoursegui',
      4 => 'ilobjfoldergui',
      5 => 'ilobjgroupgui',
      6 => 'ilobjrootfoldergui',
      7 => 'ilobjectcopygui',
      8 => 'ilpropertyformgui',
    ),
    'parents' => 
    array (
      0 => 'ilsearchcontrollergui',
    ),
  ),
  'ilappointmentpresentationbookingpoolgui' => 
  array (
    'cid' => '2x',
    'class_name' => 'ilAppointmentPresentationBookingPoolGUI',
    'class_path' => './Services/Calendar/classes/AppointmentPresentation/class.ilAppointmentPresentationBookingPoolGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilcalendarappointmentpresentationgui',
    ),
  ),
  'ilappointmentpresentationconsultationhoursgui' => 
  array (
    'cid' => '2y',
    'class_name' => 'ilAppointmentPresentationConsultationHoursGUI',
    'class_path' => './Services/Calendar/classes/AppointmentPresentation/class.ilAppointmentPresentationConsultationHoursGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilcalendarappointmentpresentationgui',
    ),
  ),
  'ilappointmentpresentationcoursegui' => 
  array (
    'cid' => '2z',
    'class_name' => 'ilAppointmentPresentationCourseGUI',
    'class_path' => './Services/Calendar/classes/AppointmentPresentation/class.ilAppointmentPresentationCourseGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilcalendarappointmentpresentationgui',
    ),
  ),
  'ilappointmentpresentationemployeetalkgui' => 
  array (
    'cid' => '30',
    'class_name' => 'ilAppointmentPresentationEmployeeTalkGUI',
    'class_path' => './Services/Calendar/classes/AppointmentPresentation/class.ilAppointmentPresentationEmployeeTalkGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilcalendarappointmentpresentationgui',
    ),
  ),
  'ilappointmentpresentationexercisegui' => 
  array (
    'cid' => '31',
    'class_name' => 'ilAppointmentPresentationExerciseGUI',
    'class_path' => './Services/Calendar/classes/AppointmentPresentation/class.ilAppointmentPresentationExerciseGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilcalendarappointmentpresentationgui',
    ),
  ),
  'ilappointmentpresentationgui' => 
  array (
    'cid' => '32',
    'class_name' => 'ilAppointmentPresentationGUI',
    'class_path' => './Services/Calendar/classes/AppointmentPresentation/class.ilAppointmentPresentationGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilcalendarappointmentpresentationgui',
    ),
  ),
  'ilappointmentpresentationgroupgui' => 
  array (
    'cid' => '33',
    'class_name' => 'ilAppointmentPresentationGroupGUI',
    'class_path' => './Services/Calendar/classes/AppointmentPresentation/class.ilAppointmentPresentationGroupGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilcalendarappointmentpresentationgui',
    ),
  ),
  'ilappointmentpresentationpublicgui' => 
  array (
    'cid' => '34',
    'class_name' => 'ilAppointmentPresentationPublicGUI',
    'class_path' => './Services/Calendar/classes/AppointmentPresentation/class.ilAppointmentPresentationPublicGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilcalendarappointmentpresentationgui',
    ),
  ),
  'ilappointmentpresentationsessiongui' => 
  array (
    'cid' => '35',
    'class_name' => 'ilAppointmentPresentationSessionGUI',
    'class_path' => './Services/Calendar/classes/AppointmentPresentation/class.ilAppointmentPresentationSessionGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilcalendarappointmentpresentationgui',
    ),
  ),
  'ilappointmentpresentationusergui' => 
  array (
    'cid' => '36',
    'class_name' => 'ilAppointmentPresentationUserGUI',
    'class_path' => './Services/Calendar/classes/AppointmentPresentation/class.ilAppointmentPresentationUserGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilcalendarappointmentpresentationgui',
    ),
  ),
  'ilassgenfeedbackpagegui' => 
  array (
    'cid' => '3a',
    'class_name' => 'ilAssGenFeedbackPageGUI',
    'class_path' => './components/ILIAS/TestQuestionPool/classes/feedback/class.ilAssGenFeedbackPageGUI.php',
    'children' => 
    array (
      0 => 'ilpageeditorgui',
      1 => 'ileditclipboardgui',
      2 => 'ilmdeditorgui',
      3 => 'ilpublicuserprofilegui',
      4 => 'ilnotegui',
      5 => 'ilpropertyformgui',
      6 => 'ilinternallinkgui',
    ),
    'parents' => 
    array (
      0 => 'ilassquestionfeedbackeditinggui',
      1 => 'ilassquestionpreviewgui',
      2 => 'illmpageobjectgui',
      3 => 'illmpresentationgui',
      4 => 'ilmytestresultsgui',
      5 => 'ilobjtestgui',
      6 => 'ilpctablegui',
      7 => 'ilparticipantstestresultsgui',
      8 => 'iltestplayerfixedquestionsetgui',
      9 => 'iltestplayerrandomquestionsetgui',
    ),
  ),
  'ilasshintpagegui' => 
  array (
    'cid' => '3b',
    'class_name' => 'ilAssHintPageGUI',
    'class_path' => './components/ILIAS/TestQuestionPool/classes/class.ilAssHintPageGUI.php',
    'children' => 
    array (
      0 => 'ilpageeditorgui',
      1 => 'ileditclipboardgui',
      2 => 'ilmdeditorgui',
      3 => 'ilpublicuserprofilegui',
      4 => 'ilnotegui',
      5 => 'ilpropertyformgui',
      6 => 'ilinternallinkgui',
    ),
    'parents' => 
    array (
      0 => 'ilassquestionhintgui',
      1 => 'ilassquestionhintrequestgui',
      2 => 'ilassquestionhintsgui',
    ),
  ),
  'ilassquestionfeedbackeditinggui' => 
  array (
    'cid' => '3l',
    'class_name' => 'ilAssQuestionFeedbackEditingGUI',
    'class_path' => './components/ILIAS/TestQuestionPool/classes/class.ilAssQuestionFeedbackEditingGUI.php',
    'children' => 
    array (
      0 => 'ilassgenfeedbackpagegui',
      1 => 'ilassspecfeedbackpagegui',
      2 => 'ilpropertyformgui',
    ),
    'parents' => 
    array (
      0 => 'illmpagegui',
      1 => 'ilobjquestionpoolgui',
      2 => 'ilobjtestgui',
    ),
  ),
  'ilassquestionhintgui' => 
  array (
    'cid' => '3n',
    'class_name' => 'ilAssQuestionHintGUI',
    'class_path' => './components/ILIAS/TestQuestionPool/classes/class.ilAssQuestionHintGUI.php',
    'children' => 
    array (
      0 => 'ilasshintpagegui',
    ),
    'parents' => 
    array (
      0 => 'ilassquestionhintsgui',
    ),
  ),
  'ilassquestionhintrequestgui' => 
  array (
    'cid' => '3o',
    'class_name' => 'ilAssQuestionHintRequestGUI',
    'class_path' => './components/ILIAS/TestQuestionPool/classes/class.ilAssQuestionHintRequestGUI.php',
    'children' => 
    array (
      0 => 'ilassquestionhintstablegui',
      1 => 'ilconfirmationgui',
      2 => 'ilpropertyformgui',
      3 => 'ilasshintpagegui',
    ),
    'parents' => 
    array (
      0 => 'ilassquestionpreviewgui',
      1 => 'iltestplayerfixedquestionsetgui',
      2 => 'iltestplayerrandomquestionsetgui',
    ),
  ),
  'ilassquestionhintsgui' => 
  array (
    'cid' => '3p',
    'class_name' => 'ilAssQuestionHintsGUI',
    'class_path' => './components/ILIAS/TestQuestionPool/classes/class.ilAssQuestionHintsGUI.php',
    'children' => 
    array (
      0 => 'ilassquestionhintgui',
      1 => 'ilassquestionhintstablegui',
      2 => 'ilasshintpagegui',
      3 => 'iltoolbargui',
      4 => 'ilconfirmationgui',
    ),
    'parents' => 
    array (
      0 => 'ilobjquestionpoolgui',
      1 => 'ilobjtestgui',
    ),
  ),
  'ilassquestionhintstablegui' => 
  array (
    'cid' => '3q',
    'class_name' => 'ilAssQuestionHintsTableGUI',
    'class_path' => './components/ILIAS/TestQuestionPool/classes/class.ilAssQuestionHintsTableGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilassquestionhintrequestgui',
      1 => 'ilassquestionhintsgui',
    ),
  ),
  'ilassquestionpagegui' => 
  array (
    'cid' => '3r',
    'class_name' => 'ilAssQuestionPageGUI',
    'class_path' => './components/ILIAS/TestQuestionPool/classes/class.ilAssQuestionPageGUI.php',
    'children' => 
    array (
      0 => 'ilpageeditorgui',
      1 => 'ileditclipboardgui',
      2 => 'ilmdeditorgui',
      3 => 'ilpublicuserprofilegui',
      4 => 'ilnotegui',
      5 => 'ilpropertyformgui',
      6 => 'ilinternallinkgui',
    ),
    'parents' => 
    array (
      0 => 'ilassquestionskillassignmentsgui',
      1 => 'ilmytestresultsgui',
      2 => 'ilmytestsolutionsgui',
      3 => 'ilobjquestionpoolgui',
      4 => 'ilobjtestgui',
      5 => 'ilparticipantstestresultsgui',
      6 => 'iltestevalobjectiveorientedgui',
      7 => 'iltestexpresspageobjectgui',
      8 => 'iltestfixedquestionsetconfiggui',
      9 => 'iltestplayerfixedquestionsetgui',
      10 => 'iltestplayerrandomquestionsetgui',
    ),
  ),
  'ilassquestionpreviewgui' => 
  array (
    'cid' => '3s',
    'class_name' => 'ilAssQuestionPreviewGUI',
    'class_path' => './components/ILIAS/TestQuestionPool/classes/class.ilAssQuestionPreviewGUI.php',
    'children' => 
    array (
      0 => 'ilassquestionpreviewtoolbargui',
      1 => 'ilassquestionrelatednavigationbargui',
      2 => 'ilassquestionhintrequestgui',
      3 => 'ilassgenfeedbackpagegui',
      4 => 'ilassspecfeedbackpagegui',
      5 => 'ilnotegui',
    ),
    'parents' => 
    array (
      0 => 'ilobjquestionpoolgui',
      1 => 'ilobjtestgui',
    ),
  ),
  'ilassquestionpreviewtoolbargui' => 
  array (
    'cid' => '3t',
    'class_name' => 'ilAssQuestionPreviewToolbarGUI',
    'class_path' => './components/ILIAS/TestQuestionPool/classes/class.ilAssQuestionPreviewToolbarGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilassquestionpreviewgui',
    ),
  ),
  'ilassquestionrelatednavigationbargui' => 
  array (
    'cid' => '3u',
    'class_name' => 'ilAssQuestionRelatedNavigationBarGUI',
    'class_path' => './components/ILIAS/TestQuestionPool/classes/class.ilAssQuestionRelatedNavigationBarGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilassquestionpreviewgui',
    ),
  ),
  'ilassquestionskillassignmentpropertyformgui' => 
  array (
    'cid' => '3v',
    'class_name' => 'ilAssQuestionSkillAssignmentPropertyFormGUI',
    'class_path' => './components/ILIAS/TestQuestionPool/classes/forms/class.ilAssQuestionSkillAssignmentPropertyFormGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilassquestionskillassignmentsgui',
    ),
  ),
  'ilassquestionskillassignmentsgui' => 
  array (
    'cid' => '3w',
    'class_name' => 'ilAssQuestionSkillAssignmentsGUI',
    'class_path' => './components/ILIAS/TestQuestionPool/classes/class.ilAssQuestionSkillAssignmentsGUI.php',
    'children' => 
    array (
      0 => 'ilassquestionskillassignmentstablegui',
      1 => 'ilskillselectorgui',
      2 => 'iltoolbargui',
      3 => 'ilassquestionskillassignmentpropertyformgui',
      4 => 'ilassquestionpagegui',
      5 => 'ilconfirmationgui',
    ),
    'parents' => 
    array (
      0 => 'ilquestionpoolskilladministrationgui',
      1 => 'iltestskilladministrationgui',
    ),
  ),
  'ilassquestionskillassignmentstablegui' => 
  array (
    'cid' => '3x',
    'class_name' => 'ilAssQuestionSkillAssignmentsTableGUI',
    'class_path' => './components/ILIAS/TestQuestionPool/classes/tables/class.ilAssQuestionSkillAssignmentsTableGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilassquestionskillassignmentsgui',
    ),
  ),
  'ilassquestionskillusagestablegui' => 
  array (
    'cid' => '3y',
    'class_name' => 'ilAssQuestionSkillUsagesTableGUI',
    'class_path' => './components/ILIAS/TestQuestionPool/classes/class.ilAssQuestionSkillUsagesTableGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilquestionpoolskilladministrationgui',
    ),
  ),
  'ilassspecfeedbackpagegui' => 
  array (
    'cid' => '40',
    'class_name' => 'ilAssSpecFeedbackPageGUI',
    'class_path' => './components/ILIAS/TestQuestionPool/classes/feedback/class.ilAssSpecFeedbackPageGUI.php',
    'children' => 
    array (
      0 => 'ilpageeditorgui',
      1 => 'ileditclipboardgui',
      2 => 'ilmdeditorgui',
      3 => 'ilpublicuserprofilegui',
      4 => 'ilnotegui',
      5 => 'ilpropertyformgui',
      6 => 'ilinternallinkgui',
    ),
    'parents' => 
    array (
      0 => 'ilassquestionfeedbackeditinggui',
      1 => 'ilassquestionpreviewgui',
      2 => 'ilmytestresultsgui',
      3 => 'ilobjtestgui',
      4 => 'ilparticipantstestresultsgui',
      5 => 'iltestplayerfixedquestionsetgui',
      6 => 'iltestplayerrandomquestionsetgui',
    ),
  ),
  'ilassignmentpresentationgui' => 
  array (
    'cid' => '44',
    'class_name' => 'ilAssignmentPresentationGUI',
    'class_path' => './components/ILIAS/Exercise/Assignment/class.ilAssignmentPresentationGUI.php',
    'children' => 
    array (
      0 => 'ilexsubmissiongui',
    ),
    'parents' => 
    array (
      0 => 'ilobjexercisegui',
    ),
  ),
  'ilauthloginpageeditorgui' => 
  array (
    'cid' => '48',
    'class_name' => 'ilAuthLoginPageEditorGUI',
    'class_path' => './Services/Authentication/classes/class.ilAuthLoginPageEditorGUI.php',
    'children' => 
    array (
      0 => 'illoginpagegui',
    ),
    'parents' => 
    array (
      0 => 'ilobjauthsettingsgui',
    ),
  ),
  'ilauthshibbolethsettingsgui' => 
  array (
    'cid' => '4a',
    'class_name' => 'ilAuthShibbolethSettingsGUI',
    'class_path' => './Services/AuthShibboleth/classes/class.ilAuthShibbolethSettingsGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjauthsettingsgui',
    ),
  ),
  'ilawarenessgui' => 
  array (
    'cid' => '4c',
    'class_name' => 'ilAwarenessGUI',
    'class_path' => './Services/Awareness/classes/class.ilAwarenessGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
    ),
  ),
  'ilbtcontrollergui' => 
  array (
    'cid' => '4d',
    'class_name' => 'ilBTControllerGUI',
    'class_path' => './Services/BackgroundTasks/classes/class.ilBTControllerGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
    ),
  ),
  'ilbadgemanagementgui' => 
  array (
    'cid' => '4i',
    'class_name' => 'ilBadgeManagementGUI',
    'class_path' => './Services/Badge/classes/class.ilBadgeManagementGUI.php',
    'children' => 
    array (
      0 => 'ilpropertyformgui',
    ),
    'parents' => 
    array (
      0 => 'ilobjbadgeadministrationgui',
      1 => 'ilobjcoursegui',
      2 => 'ilobjgroupgui',
    ),
  ),
  'ilbadgeprofilegui' => 
  array (
    'cid' => '4k',
    'class_name' => 'ilBadgeProfileGUI',
    'class_path' => './Services/Badge/classes/class.ilBadgeProfileGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilachievementsgui',
    ),
  ),
  'ilbasicskillgui' => 
  array (
    'cid' => '4p',
    'class_name' => 'ilBasicSkillGUI',
    'class_path' => './Services/Skill/Node/class.ilBasicSkillGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjskillmanagementgui',
      1 => 'ilobjskilltreegui',
    ),
  ),
  'ilbasicskilltemplategui' => 
  array (
    'cid' => '4q',
    'class_name' => 'ilBasicSkillTemplateGUI',
    'class_path' => './Services/Skill/Node/class.ilBasicSkillTemplateGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjskillmanagementgui',
      1 => 'ilobjskilltreegui',
    ),
  ),
  'ilbibladminbibtexfieldgui' => 
  array (
    'cid' => '4s',
    'class_name' => 'ilBiblAdminBibtexFieldGUI',
    'class_path' => './components/ILIAS/Bibliographic/classes/Field/class.ilBiblAdminBibtexFieldGUI.php',
    'children' => 
    array (
      0 => 'ilbibltranslationgui',
    ),
    'parents' => 
    array (
      0 => 'ilobjbibliographicadmingui',
    ),
  ),
  'ilbibladminfieldgui' => 
  array (
    'cid' => '4t',
    'class_name' => 'ilBiblAdminFieldGUI',
    'class_path' => './components/ILIAS/Bibliographic/classes/Field/class.ilBiblAdminFieldGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjbibliographicadmingui',
    ),
  ),
  'ilbibladminrisfieldgui' => 
  array (
    'cid' => '4v',
    'class_name' => 'ilBiblAdminRisFieldGUI',
    'class_path' => './components/ILIAS/Bibliographic/classes/Field/class.ilBiblAdminRisFieldGUI.php',
    'children' => 
    array (
      0 => 'ilbibltranslationgui',
    ),
    'parents' => 
    array (
      0 => 'ilobjbibliographicadmingui',
    ),
  ),
  'ilbiblentrytablegui' => 
  array (
    'cid' => '4x',
    'class_name' => 'ilBiblEntryTableGUI',
    'class_path' => './components/ILIAS/Bibliographic/classes/Entry/class.ilBiblEntryTableGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjbibliographicgui',
    ),
  ),
  'ilbiblfieldfiltergui' => 
  array (
    'cid' => '50',
    'class_name' => 'ilBiblFieldFilterGUI',
    'class_path' => './components/ILIAS/Bibliographic/classes/FieldFilter/class.ilBiblFieldFilterGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjbibliographicgui',
    ),
  ),
  'ilbibllibrarygui' => 
  array (
    'cid' => '54',
    'class_name' => 'ilBiblLibraryGUI',
    'class_path' => './components/ILIAS/Bibliographic/classes/Admin/Library/class.ilBiblLibraryGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjbibliographicadmingui',
    ),
  ),
  'ilbibltranslationgui' => 
  array (
    'cid' => '57',
    'class_name' => 'ilBiblTranslationGUI',
    'class_path' => './components/ILIAS/Bibliographic/classes/Translation/class.ilBiblTranslationGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilbibladminbibtexfieldgui',
      1 => 'ilbibladminrisfieldgui',
    ),
  ),
  'ilblogexercisegui' => 
  array (
    'cid' => '5b',
    'class_name' => 'ilBlogExerciseGUI',
    'class_path' => './components/ILIAS/Blog/Exercise/class.ilBlogExerciseGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjbloggui',
    ),
  ),
  'ilblogpostinggui' => 
  array (
    'cid' => '5d',
    'class_name' => 'ilBlogPostingGUI',
    'class_path' => './components/ILIAS/Blog/Posting/class.ilBlogPostingGUI.php',
    'children' => 
    array (
      0 => 'ilpageeditorgui',
      1 => 'ileditclipboardgui',
      2 => 'ilratinggui',
      3 => 'ilpublicuserprofilegui',
      4 => 'ilpageobjectgui',
      5 => 'ilnotegui',
    ),
    'parents' => 
    array (
      0 => 'ilobjbloggui',
      1 => 'ilportfoliopagegui',
    ),
  ),
  'ilbookbulkcreationgui' => 
  array (
    'cid' => '5e',
    'class_name' => 'ilBookBulkCreationGUI',
    'class_path' => './components/ILIAS/BookingManager/Objects/class.ilBookBulkCreationGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilbookingobjectgui',
    ),
  ),
  'ilbookinggatewaygui' => 
  array (
    'cid' => '5i',
    'class_name' => 'ilBookingGatewayGUI',
    'class_path' => './components/ILIAS/BookingManager/BookingService/class.ilBookingGatewayGUI.php',
    'children' => 
    array (
      0 => 'ilpropertyformgui',
      1 => 'ilbookingobjectservicegui',
      2 => 'ilbookingreservationsgui',
    ),
    'parents' => 
    array (
      0 => 'ilobjcoursegui',
      1 => 'ilobjsessiongui',
    ),
  ),
  'ilbookingobjectgui' => 
  array (
    'cid' => '5j',
    'class_name' => 'ilBookingObjectGUI',
    'class_path' => './components/ILIAS/BookingManager/Objects/class.ilBookingObjectGUI.php',
    'children' => 
    array (
      0 => 'ilpropertyformgui',
      1 => 'ilbookingprocesswithschedulegui',
      2 => 'ilbookingprocesswithoutschedulegui',
      3 => 'ilbookbulkcreationgui',
    ),
    'parents' => 
    array (
      0 => 'ilobjbookingpoolgui',
    ),
  ),
  'ilbookingobjectservicegui' => 
  array (
    'cid' => '5k',
    'class_name' => 'ilBookingObjectServiceGUI',
    'class_path' => './components/ILIAS/BookingManager/BookingService/class.ilBookingObjectServiceGUI.php',
    'children' => 
    array (
      0 => 'ilpropertyformgui',
      1 => 'ilbookingprocesswithschedulegui',
      2 => 'ilbookingprocesswithoutschedulegui',
    ),
    'parents' => 
    array (
      0 => 'ilbookinggatewaygui',
    ),
  ),
  'ilbookingparticipantgui' => 
  array (
    'cid' => '5m',
    'class_name' => 'ilBookingParticipantGUI',
    'class_path' => './components/ILIAS/BookingManager/Participants/class.ilBookingParticipantGUI.php',
    'children' => 
    array (
      0 => 'ilrepositorysearchgui',
    ),
    'parents' => 
    array (
      0 => 'ilobjbookingpoolgui',
    ),
  ),
  'ilbookingpreferencesgui' => 
  array (
    'cid' => '5o',
    'class_name' => 'ilBookingPreferencesGUI',
    'class_path' => './components/ILIAS/BookingManager/Preferences/class.ilBookingPreferencesGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjbookingpoolgui',
    ),
  ),
  'ilbookingprocesswithschedulegui' => 
  array (
    'cid' => '5p',
    'class_name' => 'ilBookingProcessWithScheduleGUI',
    'class_path' => './components/ILIAS/BookingManager/BookingProcess/class.ilBookingProcessWithScheduleGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilbookingobjectgui',
      1 => 'ilbookingobjectservicegui',
    ),
  ),
  'ilbookingprocesswithoutschedulegui' => 
  array (
    'cid' => '5q',
    'class_name' => 'ilBookingProcessWithoutScheduleGUI',
    'class_path' => './components/ILIAS/BookingManager/BookingProcess/class.ilBookingProcessWithoutScheduleGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilbookingobjectgui',
      1 => 'ilbookingobjectservicegui',
    ),
  ),
  'ilbookingreservationsgui' => 
  array (
    'cid' => '5r',
    'class_name' => 'ilBookingReservationsGUI',
    'class_path' => './components/ILIAS/BookingManager/Reservations/class.ilBookingReservationsGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilbookinggatewaygui',
      1 => 'ilobjbookingpoolgui',
    ),
  ),
  'ilbookingschedulegui' => 
  array (
    'cid' => '5t',
    'class_name' => 'ilBookingScheduleGUI',
    'class_path' => './components/ILIAS/BookingManager/Schedule/class.ilBookingScheduleGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjbookingpoolgui',
    ),
  ),
  'ilbuddysystemgui' => 
  array (
    'cid' => '5v',
    'class_name' => 'ilBuddySystemGUI',
    'class_path' => './Services/Contact/BuddySystem/classes/class.ilBuddySystemGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'iluipluginroutergui',
      1 => 'ilpublicuserprofilegui',
      2 => 'ilmailsearchcoursesgui',
      3 => 'ilmailsearchgroupsgui',
    ),
  ),
  'ilcassettingsgui' => 
  array (
    'cid' => '5x',
    'class_name' => 'ilCASSettingsGUI',
    'class_path' => './Services/CAS/classes/class.ilCASSettingsGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjauthsettingsgui',
    ),
  ),
  'ilcalendaragendalistgui' => 
  array (
    'cid' => '5z',
    'class_name' => 'ilCalendarAgendaListGUI',
    'class_path' => './Services/Calendar/classes/Agenda/class.ilCalendarAgendaListGUI.php',
    'children' => 
    array (
      0 => 'ilcalendarappointmentpresentationgui',
    ),
    'parents' => 
    array (
      0 => 'ilcalendarinboxgui',
    ),
  ),
  'ilcalendarappointmentgui' => 
  array (
    'cid' => '60',
    'class_name' => 'ilCalendarAppointmentGUI',
    'class_path' => './Services/Calendar/classes/class.ilCalendarAppointmentGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilcalendarappointmentpresentationgui',
      1 => 'ilcalendarblockgui',
      2 => 'ilcalendarcategorygui',
      3 => 'ilcalendardaygui',
      4 => 'ilcalendarinboxgui',
      5 => 'ilcalendarmonthgui',
      6 => 'ilcalendarpresentationgui',
      7 => 'ilcalendarweekgui',
      8 => 'ilpdcalendarblockgui',
    ),
  ),
  'ilcalendarappointmentpresentationgui' => 
  array (
    'cid' => '62',
    'class_name' => 'ilCalendarAppointmentPresentationGUI',
    'class_path' => './Services/Calendar/classes/class.ilCalendarAppointmentPresentationGUI.php',
    'children' => 
    array (
      0 => 'ilinfoscreengui',
      1 => 'ilcalendarappointmentgui',
      2 => 'ilappointmentpresentationbookingpoolgui',
      3 => 'ilappointmentpresentationconsultationhoursgui',
      4 => 'ilappointmentpresentationcoursegui',
      5 => 'ilappointmentpresentationemployeetalkgui',
      6 => 'ilappointmentpresentationexercisegui',
      7 => 'ilappointmentpresentationgui',
      8 => 'ilappointmentpresentationgroupgui',
      9 => 'ilappointmentpresentationpublicgui',
      10 => 'ilappointmentpresentationsessiongui',
      11 => 'ilappointmentpresentationusergui',
    ),
    'parents' => 
    array (
      0 => 'ilcalendaragendalistgui',
      1 => 'ilcalendarblockgui',
      2 => 'ilcalendardaygui',
      3 => 'ilcalendarmonthgui',
      4 => 'ilcalendarweekgui',
      5 => 'ilpdcalendarblockgui',
    ),
  ),
  'ilcalendarblockgui' => 
  array (
    'cid' => '64',
    'class_name' => 'ilCalendarBlockGUI',
    'class_path' => './Services/Calendar/classes/class.ilCalendarBlockGUI.php',
    'children' => 
    array (
      0 => 'ilcalendarappointmentgui',
      1 => 'ilcalendarmonthgui',
      2 => 'ilcalendarweekgui',
      3 => 'ilcalendardaygui',
      4 => 'ilconsultationhoursgui',
      5 => 'ilcalendarappointmentpresentationgui',
    ),
    'parents' => 
    array (
      0 => 'ilcolumngui',
      1 => 'ilcalendarpresentationgui',
    ),
  ),
  'ilcalendarcategorygui' => 
  array (
    'cid' => '65',
    'class_name' => 'ilCalendarCategoryGUI',
    'class_path' => './Services/Calendar/classes/class.ilCalendarCategoryGUI.php',
    'children' => 
    array (
      0 => 'ilcalendarappointmentgui',
      1 => 'ilcalendarselectionblockgui',
    ),
    'parents' => 
    array (
      0 => 'ilcalendarpresentationgui',
    ),
  ),
  'ilcalendardaygui' => 
  array (
    'cid' => '67',
    'class_name' => 'ilCalendarDayGUI',
    'class_path' => './Services/Calendar/classes/class.ilCalendarDayGUI.php',
    'children' => 
    array (
      0 => 'ilcalendarappointmentgui',
      1 => 'ilcalendarappointmentpresentationgui',
    ),
    'parents' => 
    array (
      0 => 'ilcalendarblockgui',
      1 => 'ilcalendarpresentationgui',
      2 => 'ilpdcalendarblockgui',
    ),
  ),
  'ilcalendarinboxgui' => 
  array (
    'cid' => '69',
    'class_name' => 'ilCalendarInboxGUI',
    'class_path' => './Services/Calendar/classes/class.ilCalendarInboxGUI.php',
    'children' => 
    array (
      0 => 'ilcalendarappointmentgui',
      1 => 'ilcalendaragendalistgui',
    ),
    'parents' => 
    array (
      0 => 'ilcalendarpresentationgui',
      1 => 'ilpdcalendarblockgui',
    ),
  ),
  'ilcalendarmonthgui' => 
  array (
    'cid' => '6c',
    'class_name' => 'ilCalendarMonthGUI',
    'class_path' => './Services/Calendar/classes/class.ilCalendarMonthGUI.php',
    'children' => 
    array (
      0 => 'ilcalendarappointmentgui',
      1 => 'ilcalendarappointmentpresentationgui',
    ),
    'parents' => 
    array (
      0 => 'ilcalendarblockgui',
      1 => 'ilcalendarpresentationgui',
      2 => 'ilpdcalendarblockgui',
      3 => 'ilportfoliopagegui',
      4 => 'ilportfoliotemplatepagegui',
    ),
  ),
  'ilcalendarpresentationgui' => 
  array (
    'cid' => '6d',
    'class_name' => 'ilCalendarPresentationGUI',
    'class_path' => './Services/Calendar/classes/class.ilCalendarPresentationGUI.php',
    'children' => 
    array (
      0 => 'ilcalendarmonthgui',
      1 => 'ilcalendarusersettingsgui',
      2 => 'ilcalendarcategorygui',
      3 => 'ilcalendarweekgui',
      4 => 'ilcalendarappointmentgui',
      5 => 'ilcalendardaygui',
      6 => 'ilcalendarinboxgui',
      7 => 'ilcalendarsubscriptiongui',
      8 => 'ilconsultationhoursgui',
      9 => 'ilcalendarblockgui',
      10 => 'ilpdcalendarblockgui',
      11 => 'ilpublicuserprofilegui',
    ),
    'parents' => 
    array (
      0 => 'ildashboardgui',
      1 => 'ilobjcoursegui',
      2 => 'ilobjgroupgui',
    ),
  ),
  'ilcalendarselectionblockgui' => 
  array (
    'cid' => '6f',
    'class_name' => 'ilCalendarSelectionBlockGUI',
    'class_path' => './Services/Calendar/classes/class.ilCalendarSelectionBlockGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilcalendarcategorygui',
    ),
  ),
  'ilcalendarsubscriptiongui' => 
  array (
    'cid' => '6j',
    'class_name' => 'ilCalendarSubscriptionGUI',
    'class_path' => './Services/Calendar/classes/class.ilCalendarSubscriptionGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilcalendarpresentationgui',
    ),
  ),
  'ilcalendarusersettingsgui' => 
  array (
    'cid' => '6k',
    'class_name' => 'ilCalendarUserSettingsGUI',
    'class_path' => './Services/Calendar/classes/class.ilCalendarUserSettingsGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilcalendarpresentationgui',
    ),
  ),
  'ilcalendarweekgui' => 
  array (
    'cid' => '6m',
    'class_name' => 'ilCalendarWeekGUI',
    'class_path' => './Services/Calendar/classes/class.ilCalendarWeekGUI.php',
    'children' => 
    array (
      0 => 'ilcalendarappointmentgui',
      1 => 'ilcalendarappointmentpresentationgui',
    ),
    'parents' => 
    array (
      0 => 'ilcalendarblockgui',
      1 => 'ilcalendarpresentationgui',
      2 => 'ilpdcalendarblockgui',
    ),
  ),
  'ilcertificategui' => 
  array (
    'cid' => '6p',
    'class_name' => 'ilCertificateGUI',
    'class_path' => './Services/Certificate/classes/class.ilCertificateGUI.php',
    'children' => 
    array (
      0 => 'ilpropertyformgui',
    ),
    'parents' => 
    array (
      0 => 'ilcmixapisettingsgui',
      1 => 'illticonsumersettingsgui',
      2 => 'ilobjcoursegui',
      3 => 'ilobjexercisegui',
      4 => 'ilobjscorm2004learningmodulegui',
      5 => 'ilobjscormlearningmodulegui',
      6 => 'ilobjstudyprogrammegui',
      7 => 'ilobjtestgui',
    ),
  ),
  'ilchatroomauthinputgui' => 
  array (
    'cid' => '6s',
    'class_name' => 'ilChatroomAuthInputGUI',
    'class_path' => './components/ILIAS/Chatroom/classes/class.ilChatroomAuthInputGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilformpropertydispatchgui',
    ),
  ),
  'ilclassificationblockgui' => 
  array (
    'cid' => '77',
    'class_name' => 'ilClassificationBlockGUI',
    'class_path' => './Services/Container/Classification/class.ilClassificationBlockGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilcolumngui',
    ),
  ),
  'ilcmixapiexportgui' => 
  array (
    'cid' => '7b',
    'class_name' => 'ilCmiXapiExportGUI',
    'class_path' => './components/ILIAS/CmiXapi/classes/class.ilCmiXapiExportGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjcmixapigui',
    ),
  ),
  'ilcmixapilaunchgui' => 
  array (
    'cid' => '7c',
    'class_name' => 'ilCmiXapiLaunchGUI',
    'class_path' => './components/ILIAS/CmiXapi/classes/class.ilCmiXapiLaunchGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjcmixapigui',
    ),
  ),
  'ilcmixapiregistrationgui' => 
  array (
    'cid' => '7e',
    'class_name' => 'ilCmiXapiRegistrationGUI',
    'class_path' => './components/ILIAS/CmiXapi/classes/class.ilCmiXapiRegistrationGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjcmixapigui',
    ),
  ),
  'ilcmixapiscoringgui' => 
  array (
    'cid' => '7f',
    'class_name' => 'ilCmiXapiScoringGUI',
    'class_path' => './components/ILIAS/CmiXapi/classes/class.ilCmiXapiScoringGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjcmixapigui',
    ),
  ),
  'ilcmixapisettingsgui' => 
  array (
    'cid' => '7h',
    'class_name' => 'ilCmiXapiSettingsGUI',
    'class_path' => './components/ILIAS/CmiXapi/classes/class.ilCmiXapiSettingsGUI.php',
    'children' => 
    array (
      0 => 'ilcertificategui',
    ),
    'parents' => 
    array (
      0 => 'ilobjcmixapigui',
    ),
  ),
  'ilcmixapistatementsgui' => 
  array (
    'cid' => '7i',
    'class_name' => 'ilCmiXapiStatementsGUI',
    'class_path' => './components/ILIAS/CmiXapi/classes/class.ilCmiXapiStatementsGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjcmixapigui',
    ),
  ),
  'ilcolumngui' => 
  array (
    'cid' => '7m',
    'class_name' => 'ilColumnGUI',
    'class_path' => './Services/Block/classes/class.ilColumnGUI.php',
    'children' => 
    array (
      0 => 'ilcalendarblockgui',
      1 => 'ilclassificationblockgui',
      2 => 'ildashboardblockgui',
      3 => 'ildashboardlearningsequencegui',
      4 => 'ilnewsforcontextblockgui',
      5 => 'ilobjectmetadatablockgui',
      6 => 'ilpdcalendarblockgui',
      7 => 'ilpdmailblockgui',
      8 => 'ilpdnewsblockgui',
      9 => 'ilpdstudyprogrammeexpandablelistgui',
      10 => 'ilpdstudyprogrammesimplelistgui',
      11 => 'ilpdtasksblockgui',
      12 => 'ilpollblockgui',
      13 => 'ilselecteditemsblockgui',
    ),
    'parents' => 
    array (
      0 => 'ilcoursecontentgui',
      1 => 'ildashboardgui',
      2 => 'ilinfoscreengui',
      3 => 'ilobjcategorygui',
      4 => 'ilobjcoursegui',
      5 => 'ilobjemployeetalkgui',
      6 => 'ilobjemployeetalkseriesgui',
      7 => 'ilobjfoldergui',
      8 => 'ilobjforumgui',
      9 => 'ilobjgroupgui',
      10 => 'ilobjlearningsequencegui',
      11 => 'ilobjorgunitgui',
      12 => 'ilobjrootfoldergui',
      13 => 'ilobjstudyprogrammegui',
      14 => 'ilobjtalktemplateadministrationgui',
      15 => 'ilobjtalktemplategui',
    ),
  ),
  'ilcommentgui' => 
  array (
    'cid' => '7o',
    'class_name' => 'ilCommentGUI',
    'class_path' => './Services/Notes/Comment/class.ilCommentGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilinfoscreengui',
      1 => 'illmpresentationgui',
      2 => 'ilnewstimelinegui',
      3 => 'ilobjmediacastgui',
      4 => 'ilpageobjectgui',
      5 => 'ilwikipagegui',
    ),
  ),
  'ilcommonactiondispatchergui' => 
  array (
    'cid' => '7p',
    'class_name' => 'ilCommonActionDispatcherGUI',
    'class_path' => './Services/Object/classes/class.ilCommonActionDispatcherGUI.php',
    'children' => 
    array (
      0 => 'ilnotegui',
      1 => 'iltagginggui',
      2 => 'ilobjectactivationgui',
      3 => 'ilratinggui',
      4 => 'ilobjrootfoldergui',
    ),
    'parents' => 
    array (
      0 => 'ildashboardblockgui',
      1 => 'ildashboardlearningsequencegui',
      2 => 'ilinfoscreengui',
      3 => 'illmpagegui',
      4 => 'illmpresentationgui',
      5 => 'ilobjbibliographicgui',
      6 => 'ilobjbloggui',
      7 => 'ilobjbookingpoolgui',
      8 => 'ilobjcategorygui',
      9 => 'ilobjchatroomgui',
      10 => 'ilobjcloudgui',
      11 => 'ilobjcmixapigui',
      12 => 'ilobjcontentobjectgui',
      13 => 'ilobjcontentpagegui',
      14 => 'ilobjcoursegui',
      15 => 'ilobjcoursereferencegui',
      16 => 'ilobjdatacollectiongui',
      17 => 'ilobjemployeetalkgui',
      18 => 'ilobjemployeetalkseriesgui',
      19 => 'ilobjexercisegui',
      20 => 'ilobjfilebasedlmgui',
      21 => 'ilobjfilegui',
      22 => 'ilobjfoldergui',
      23 => 'ilobjforumgui',
      24 => 'ilobjglossarygui',
      25 => 'ilobjgroupgui',
      26 => 'ilobjindividualassessmentgui',
      27 => 'ilobjitemgroupgui',
      28 => 'ilobjlticonsumergui',
      29 => 'ilobjlearningmodulegui',
      30 => 'ilobjlearningsequencegui',
      31 => 'ilobjlinkresourcegui',
      32 => 'ilobjmediacastgui',
      33 => 'ilobjmediapoolgui',
      34 => 'ilobjorgunitgui',
      35 => 'ilobjpollgui',
      36 => 'ilobjportfoliogui',
      37 => 'ilobjportfoliotemplategui',
      38 => 'ilobjquestionpoolgui',
      39 => 'ilobjremotecategorygui',
      40 => 'ilobjremotecoursegui',
      41 => 'ilobjremotefilegui',
      42 => 'ilobjremoteglossarygui',
      43 => 'ilobjremotegroupgui',
      44 => 'ilobjremotelearningmodulegui',
      45 => 'ilobjremotetestgui',
      46 => 'ilobjremotewikigui',
      47 => 'ilobjrootfoldergui',
      48 => 'ilobjsahslearningmodulegui',
      49 => 'ilobjscorm2004learningmodulegui',
      50 => 'ilobjsessiongui',
      51 => 'ilobjstudyprogrammegui',
      52 => 'ilobjsurveygui',
      53 => 'ilobjsurveyquestionpoolgui',
      54 => 'ilobjtalktemplateadministrationgui',
      55 => 'ilobjtalktemplategui',
      56 => 'ilobjtestgui',
      57 => 'ilobjwikigui',
      58 => 'ilobjworkspacefoldergui',
      59 => 'ilobjworkspacerootfoldergui',
      60 => 'ilpdnewsgui',
      61 => 'ilwikipagegui',
    ),
  ),
  'ilconditionhandlergui' => 
  array (
    'cid' => '7q',
    'class_name' => 'ilConditionHandlerGUI',
    'class_path' => './Services/Conditions/classes/class.ilConditionHandlerGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'illoeditorgui',
      1 => 'ilobjcoursegui',
      2 => 'ilobjectactivationgui',
      3 => 'ilstructureobjectgui',
    ),
  ),
  'ilconfirmationgui' => 
  array (
    'cid' => '7s',
    'class_name' => 'ilConfirmationGUI',
    'class_path' => './Services/UIComponent/Confirmation/class.ilConfirmationGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilassquestionhintrequestgui',
      1 => 'ilassquestionhintsgui',
      2 => 'ilassquestionskillassignmentsgui',
      3 => 'ilobjtestsettingsmaingui',
      4 => 'ilobjtestsettingsscoringresultsgui',
      5 => 'iltestplayerfixedquestionsetgui',
      6 => 'iltestplayerrandomquestionsetgui',
    ),
  ),
  'ilconsultationhoursgui' => 
  array (
    'cid' => '7w',
    'class_name' => 'ilConsultationHoursGUI',
    'class_path' => './Services/Calendar/classes/ConsultationHours/class.ilConsultationHoursGUI.php',
    'children' => 
    array (
      0 => 'ilpublicuserprofilegui',
      1 => 'ilrepositorysearchgui',
    ),
    'parents' => 
    array (
      0 => 'ilcalendarblockgui',
      1 => 'ilcalendarpresentationgui',
      2 => 'ilpdcalendarblockgui',
      3 => 'ilportfoliopagegui',
      4 => 'ilportfoliotemplatepagegui',
    ),
  ),
  'ilcontskilladmingui' => 
  array (
    'cid' => '7z',
    'class_name' => 'ilContSkillAdminGUI',
    'class_path' => './Services/Container/Skills/classes/class.ilContSkillAdminGUI.php',
    'children' => 
    array (
      0 => 'ilskillprofilegui',
      1 => 'ilskillprofileuploadhandlergui',
    ),
    'parents' => 
    array (
      0 => 'ilcontainerskillgui',
    ),
  ),
  'ilcontskillpresentationgui' => 
  array (
    'cid' => '81',
    'class_name' => 'ilContSkillPresentationGUI',
    'class_path' => './Services/Container/Skills/classes/class.ilContSkillPresentationGUI.php',
    'children' => 
    array (
      0 => 'ilpersonalskillsgui',
    ),
    'parents' => 
    array (
      0 => 'ilcontainerskillgui',
    ),
  ),
  'ilcontactgui' => 
  array (
    'cid' => '83',
    'class_name' => 'ilContactGUI',
    'class_path' => './Services/Contact/classes/class.ilContactGUI.php',
    'children' => 
    array (
      0 => 'ilmailsearchcoursesgui',
      1 => 'ilmailsearchgroupsgui',
      2 => 'ilmailinglistsgui',
      3 => 'ilusersgallerygui',
      4 => 'ilpublicuserprofilegui',
    ),
    'parents' => 
    array (
      0 => 'ildashboardgui',
      1 => 'ilmailgui',
    ),
  ),
  'ilcontainerblockpropertiesstoragegui' => 
  array (
    'cid' => '84',
    'class_name' => 'ilContainerBlockPropertiesStorageGUI',
    'class_path' => './Services/Container/Content/class.ilContainerBlockPropertiesStorageGUI.php',
    'children' => 
    array (
      0 => 'ilcontainerblockpropertiesstoragegui',
    ),
    'parents' => 
    array (
      0 => 'ilcontainerblockpropertiesstoragegui',
    ),
  ),
  'ilcontainerfilteradmingui' => 
  array (
    'cid' => '87',
    'class_name' => 'ilContainerFilterAdminGUI',
    'class_path' => './Services/Container/Filter/classes/class.ilContainerFilterAdminGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjcategorygui',
    ),
  ),
  'ilcontainergui' => 
  array (
    'cid' => '89',
    'class_name' => 'ilContainerGUI',
    'class_path' => './Services/Container/classes/class.ilContainerGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'iladvancedsearchgui',
      1 => 'illuceneadvancedsearchgui',
      2 => 'illucenesearchgui',
      3 => 'ilobjstudyprogrammegui',
      4 => 'ilsearchgui',
    ),
  ),
  'ilcontainernewssettingsgui' => 
  array (
    'cid' => '8b',
    'class_name' => 'ilContainerNewsSettingsGUI',
    'class_path' => './Services/Container/News/class.ilContainerNewsSettingsGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjcategorygui',
      1 => 'ilobjcoursegui',
      2 => 'ilobjforumgui',
      3 => 'ilobjgroupgui',
    ),
  ),
  'ilcontainerpagegui' => 
  array (
    'cid' => '8d',
    'class_name' => 'ilContainerPageGUI',
    'class_path' => './Services/Container/Page/class.ilContainerPageGUI.php',
    'children' => 
    array (
      0 => 'ilpageeditorgui',
      1 => 'ileditclipboardgui',
      2 => 'ilmdeditorgui',
      3 => 'ilpublicuserprofilegui',
      4 => 'ilnotegui',
      5 => 'ilpropertyformgui',
      6 => 'ilinternallinkgui',
      7 => 'ilpagemultilanggui',
    ),
    'parents' => 
    array (
      0 => 'ilobjcategorygui',
      1 => 'ilobjcoursegui',
      2 => 'ilobjfoldergui',
      3 => 'ilobjgroupgui',
      4 => 'ilobjrootfoldergui',
      5 => 'ilobjstudyprogrammegui',
    ),
  ),
  'ilcontainerskillgui' => 
  array (
    'cid' => '8h',
    'class_name' => 'ilContainerSkillGUI',
    'class_path' => './Services/Container/Skills/classes/class.ilContainerSkillGUI.php',
    'children' => 
    array (
      0 => 'ilcontskillpresentationgui',
      1 => 'ilcontskilladmingui',
    ),
    'parents' => 
    array (
      0 => 'ilobjcoursegui',
      1 => 'ilobjgroupgui',
    ),
  ),
  'ilcontainerstartobjectsgui' => 
  array (
    'cid' => '8k',
    'class_name' => 'ilContainerStartObjectsGUI',
    'class_path' => './Services/Container/StartObjects/class.ilContainerStartObjectsGUI.php',
    'children' => 
    array (
      0 => 'ilcontainerstartobjectspagegui',
    ),
    'parents' => 
    array (
      0 => 'illoeditorgui',
      1 => 'ilobjcoursegui',
    ),
  ),
  'ilcontainerstartobjectspagegui' => 
  array (
    'cid' => '8l',
    'class_name' => 'ilContainerStartObjectsPageGUI',
    'class_path' => './Services/Container/StartObjects/class.ilContainerStartObjectsPageGUI.php',
    'children' => 
    array (
      0 => 'ilpageeditorgui',
      1 => 'ileditclipboardgui',
      2 => 'ilmdeditorgui',
      3 => 'ilpublicuserprofilegui',
      4 => 'ilnotegui',
      5 => 'ilpropertyformgui',
      6 => 'ilinternallinkgui',
      7 => 'ilpagemultilanggui',
    ),
    'parents' => 
    array (
      0 => 'ilcontainerstartobjectsgui',
      1 => 'ilobjcoursegui',
    ),
  ),
  'ilcontentpagepagegui' => 
  array (
    'cid' => '8n',
    'class_name' => 'ilContentPagePageGUI',
    'class_path' => './components/ILIAS/ContentPage/classes/class.ilContentPagePageGUI.php',
    'children' => 
    array (
      0 => 'ilpageeditorgui',
      1 => 'ileditclipboardgui',
      2 => 'ilmdeditorgui',
      3 => 'ilpublicuserprofilegui',
      4 => 'ilnotegui',
      5 => 'ilpropertyformgui',
      6 => 'ilinternallinkgui',
      7 => 'ilpagemultilanggui',
    ),
    'parents' => 
    array (
      0 => 'ilobjcontentpagegui',
    ),
  ),
  'ilcontentstyleimagegui' => 
  array (
    'cid' => '8o',
    'class_name' => 'ilContentStyleImageGUI',
    'class_path' => './Services/Style/Content/Images/class.ilContentStyleImageGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjstylesheetgui',
    ),
  ),
  'ilcontentstylesettingsgui' => 
  array (
    'cid' => '8p',
    'class_name' => 'ilContentStyleSettingsGUI',
    'class_path' => './Services/Style/Content/classes/class.ilContentStyleSettingsGUI.php',
    'children' => 
    array (
      0 => 'ilobjstylesheetgui',
    ),
    'parents' => 
    array (
      0 => 'ilobjstylesettingsgui',
    ),
  ),
  'ilcoursecontentgui' => 
  array (
    'cid' => '8u',
    'class_name' => 'ilCourseContentGUI',
    'class_path' => './components/ILIAS/Course/classes/class.ilCourseContentGUI.php',
    'children' => 
    array (
      0 => 'ilcolumngui',
      1 => 'ilobjectcopygui',
    ),
    'parents' => 
    array (
      0 => 'ilobjcoursegui',
      1 => 'ilobjfoldergui',
      2 => 'ilobjgroupgui',
    ),
  ),
  'ilcoursemembershipgui' => 
  array (
    'cid' => '90',
    'class_name' => 'ilCourseMembershipGUI',
    'class_path' => './components/ILIAS/Course/classes/class.ilCourseMembershipGUI.php',
    'children' => 
    array (
      0 => 'ilmailmembersearchgui',
      1 => 'ilusersgallerygui',
      2 => 'ilrepositorysearchgui',
      3 => 'ilcourseparticipantsgroupsgui',
      4 => 'ilobjectcustomuserfieldsgui',
      5 => 'ilsessionoverviewgui',
      6 => 'ilmemberexportgui',
    ),
    'parents' => 
    array (
      0 => 'ilobjcoursegui',
    ),
  ),
  'ilcourseobjectivesgui' => 
  array (
    'cid' => '95',
    'class_name' => 'ilCourseObjectivesGUI',
    'class_path' => './components/ILIAS/Course/classes/Objectives/class.ilCourseObjectivesGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
      1 => 'illoeditorgui',
      2 => 'ilobjcoursegui',
      3 => 'ilrepositorygui',
    ),
  ),
  'ilcourseparticipantsgroupsgui' => 
  array (
    'cid' => '97',
    'class_name' => 'ilCourseParticipantsGroupsGUI',
    'class_path' => './components/ILIAS/Course/classes/class.ilCourseParticipantsGroupsGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilcoursemembershipgui',
      1 => 'ilgroupmembershipgui',
      2 => 'illearningsequencemembershipgui',
      3 => 'ilobjcoursegui',
    ),
  ),
  'ilcourseregistrationgui' => 
  array (
    'cid' => '9b',
    'class_name' => 'ilCourseRegistrationGUI',
    'class_path' => './components/ILIAS/Course/classes/class.ilCourseRegistrationGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjcoursegui',
    ),
  ),
  'ilcronmanagergui' => 
  array (
    'cid' => '9e',
    'class_name' => 'ilCronManagerGUI',
    'class_path' => './Services/Cron/classes/class.ilCronManagerGUI.php',
    'children' => 
    array (
      0 => 'ilpropertyformgui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
      1 => 'ilobjsystemfoldergui',
    ),
  ),
  'ilcustomuserfieldsgui' => 
  array (
    'cid' => '9i',
    'class_name' => 'ilCustomUserFieldsGUI',
    'class_path' => './Services/User/classes/class.ilCustomUserFieldsGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjuserfoldergui',
    ),
  ),
  'ildashboardblockgui' => 
  array (
    'cid' => '9k',
    'class_name' => 'ilDashboardBlockGUI',
    'class_path' => './Services/Dashboard/Block/classes/class.ilDashboardBlockGUI.php',
    'children' => 
    array (
      0 => 'ilcommonactiondispatchergui',
    ),
    'parents' => 
    array (
      0 => 'ilcolumngui',
    ),
  ),
  'ildashboardgui' => 
  array (
    'cid' => '9m',
    'class_name' => 'ilDashboardGUI',
    'class_path' => './Services/Dashboard/classes/class.ilDashboardGUI.php',
    'children' => 
    array (
      0 => 'ilpersonalprofilegui',
      1 => 'ilobjusergui',
      2 => 'ilpdnotesgui',
      3 => 'ilcolumngui',
      4 => 'ilpdnewsgui',
      5 => 'ilcalendarpresentationgui',
      6 => 'ilmailsearchgui',
      7 => 'ilcontactgui',
      8 => 'ilpersonalworkspacegui',
      9 => 'ilpersonalsettingsgui',
      10 => 'ilportfoliorepositorygui',
      11 => 'ilobjchatroomgui',
      12 => 'ilmystaffgui',
      13 => 'ilgroupuseractionsgui',
      14 => 'ilachievementsgui',
      15 => 'ilpdmailblockgui',
      16 => 'ilselecteditemsblockgui',
      17 => 'ildashboardrecommendedcontentgui',
      18 => 'ilmembershipblockgui',
      19 => 'ildashboardlearningsequencegui',
      20 => 'ilstudyprogrammedashboardviewgui',
      21 => 'ilobjstudyprogrammegui',
      22 => 'ilobjbibliographicuploadhandlergui',
      23 => 'ilobjfileuploadhandlergui',
      24 => 'ilobjlanguageextgui',
    ),
    'parents' => 
    array (
      0 => 'ilstartupgui',
    ),
  ),
  'ildashboardlearningsequencegui' => 
  array (
    'cid' => '9n',
    'class_name' => 'ilDashboardLearningSequenceGUI',
    'class_path' => './components/ILIAS/LearningSequence/classes/class.ilDashboardLearningSequenceGUI.php',
    'children' => 
    array (
      0 => 'ilcommonactiondispatchergui',
    ),
    'parents' => 
    array (
      0 => 'ilcolumngui',
      1 => 'ildashboardgui',
    ),
  ),
  'ildashboardrecommendedcontentgui' => 
  array (
    'cid' => '9o',
    'class_name' => 'ilDashboardRecommendedContentGUI',
    'class_path' => './Services/Repository/RecommendedContent/classes/class.ilDashboardRecommendedContentGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ildashboardgui',
    ),
  ),
  'ildclcreateviewdefinitiongui' => 
  array (
    'cid' => '9t',
    'class_name' => 'ilDclCreateViewDefinitionGUI',
    'class_path' => './components/ILIAS/DataCollection/classes/CreateView/class.ilDclCreateViewDefinitionGUI.php',
    'children' => 
    array (
      0 => 'ilpageeditorgui',
      1 => 'ileditclipboardgui',
      2 => 'ilpublicuserprofilegui',
      3 => 'ilpageobjectgui',
    ),
    'parents' => 
    array (
      0 => 'ildcltablevieweditgui',
    ),
  ),
  'ildcldetailedviewdefinitiongui' => 
  array (
    'cid' => '9v',
    'class_name' => 'ilDclDetailedViewDefinitionGUI',
    'class_path' => './components/ILIAS/DataCollection/classes/DetailedView/class.ilDclDetailedViewDefinitionGUI.php',
    'children' => 
    array (
      0 => 'ilpageeditorgui',
      1 => 'ileditclipboardgui',
      2 => 'ilpublicuserprofilegui',
      3 => 'ilpageobjectgui',
    ),
    'parents' => 
    array (
      0 => 'ildcldetailedviewgui',
      1 => 'ildcltablevieweditgui',
    ),
  ),
  'ildcldetailedviewgui' => 
  array (
    'cid' => '9w',
    'class_name' => 'ilDclDetailedViewGUI',
    'class_path' => './components/ILIAS/DataCollection/classes/DetailedView/class.ilDclDetailedViewGUI.php',
    'children' => 
    array (
      0 => 'ildcldetailedviewdefinitiongui',
      1 => 'ileditclipboardgui',
    ),
    'parents' => 
    array (
      0 => 'ilobjdatacollectiongui',
    ),
  ),
  'ildcleditviewdefinitiongui' => 
  array (
    'cid' => '9x',
    'class_name' => 'ilDclEditViewDefinitionGUI',
    'class_path' => './components/ILIAS/DataCollection/classes/EditView/class.ilDclEditViewDefinitionGUI.php',
    'children' => 
    array (
      0 => 'ilpageeditorgui',
      1 => 'ileditclipboardgui',
      2 => 'ilpublicuserprofilegui',
      3 => 'ilpageobjectgui',
    ),
    'parents' => 
    array (
      0 => 'ildcltablevieweditgui',
    ),
  ),
  'ildclexportgui' => 
  array (
    'cid' => '9z',
    'class_name' => 'ilDclExportGUI',
    'class_path' => './components/ILIAS/DataCollection/classes/class.ilDclExportGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjdatacollectiongui',
    ),
  ),
  'ildclfieldeditgui' => 
  array (
    'cid' => 'a1',
    'class_name' => 'ilDclFieldEditGUI',
    'class_path' => './components/ILIAS/DataCollection/classes/Fields/class.ilDclFieldEditGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ildcltablelistgui',
    ),
  ),
  'ildclfieldlistgui' => 
  array (
    'cid' => 'a2',
    'class_name' => 'ilDclFieldListGUI',
    'class_path' => './components/ILIAS/DataCollection/classes/Fields/class.ilDclFieldListGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ildcltablelistgui',
    ),
  ),
  'ildclpropertyformgui' => 
  array (
    'cid' => 'a5',
    'class_name' => 'ilDclPropertyFormGUI',
    'class_path' => './components/ILIAS/DataCollection/classes/Helpers/class.ilDclPropertyFormGUI.php',
    'children' => 
    array (
      0 => 'ilformpropertydispatchgui',
    ),
    'parents' => 
    array (
      0 => 'ilobjdatacollectiongui',
    ),
  ),
  'ildclrecordeditgui' => 
  array (
    'cid' => 'a6',
    'class_name' => 'ilDclRecordEditGUI',
    'class_path' => './components/ILIAS/DataCollection/classes/Content/class.ilDclRecordEditGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjdatacollectiongui',
    ),
  ),
  'ildclrecordlistgui' => 
  array (
    'cid' => 'a7',
    'class_name' => 'ilDclRecordListGUI',
    'class_path' => './components/ILIAS/DataCollection/classes/Content/class.ilDclRecordListGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjdatacollectiongui',
    ),
  ),
  'ildcltableeditgui' => 
  array (
    'cid' => 'a9',
    'class_name' => 'ilDclTableEditGUI',
    'class_path' => './components/ILIAS/DataCollection/classes/Table/class.ilDclTableEditGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ildcltablelistgui',
    ),
  ),
  'ildcltablelistgui' => 
  array (
    'cid' => 'aa',
    'class_name' => 'ilDclTableListGUI',
    'class_path' => './components/ILIAS/DataCollection/classes/Table/class.ilDclTableListGUI.php',
    'children' => 
    array (
      0 => 'ildclfieldlistgui',
      1 => 'ildclfieldeditgui',
      2 => 'ildcltableviewgui',
      3 => 'ildcltableeditgui',
    ),
    'parents' => 
    array (
      0 => 'ilobjdatacollectiongui',
    ),
  ),
  'ildcltablevieweditgui' => 
  array (
    'cid' => 'ae',
    'class_name' => 'ilDclTableViewEditGUI',
    'class_path' => './components/ILIAS/DataCollection/classes/TableView/class.ilDclTableViewEditGUI.php',
    'children' => 
    array (
      0 => 'ildcldetailedviewdefinitiongui',
      1 => 'ildclcreateviewdefinitiongui',
      2 => 'ildcleditviewdefinitiongui',
    ),
    'parents' => 
    array (
      0 => 'ildcltableviewgui',
    ),
  ),
  'ildcltableviewgui' => 
  array (
    'cid' => 'af',
    'class_name' => 'ilDclTableViewGUI',
    'class_path' => './components/ILIAS/DataCollection/classes/TableView/class.ilDclTableViewGUI.php',
    'children' => 
    array (
      0 => 'ildcltablevieweditgui',
    ),
    'parents' => 
    array (
      0 => 'ildcltablelistgui',
    ),
  ),
  'ilderivedtasksgui' => 
  array (
    'cid' => 'ai',
    'class_name' => 'ilDerivedTasksGUI',
    'class_path' => './Services/Tasks/DerivedTasks/classes/class.ilDerivedTasksGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilstartupgui',
    ),
  ),
  'ildidactictemplategui' => 
  array (
    'cid' => 'aj',
    'class_name' => 'ilDidacticTemplateGUI',
    'class_path' => './Services/DidacticTemplate/classes/class.ilDidacticTemplateGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilpermissiongui',
      1 => 'ilobjbookingpoolgui',
      2 => 'ilobjcategorygui',
      3 => 'ilobjcoursegui',
      4 => 'ilobjfoldergui',
      5 => 'ilobjitemgroupgui',
      6 => 'ilobjorgunitgui',
    ),
  ),
  'ildidactictemplatesettingsgui' => 
  array (
    'cid' => 'ak',
    'class_name' => 'ilDidacticTemplateSettingsGUI',
    'class_path' => './Services/DidacticTemplate/classes/Setting/class.ilDidacticTemplateSettingsGUI.php',
    'children' => 
    array (
      0 => 'ilmultilingualismgui',
      1 => 'ilpropertyformgui',
    ),
    'parents' => 
    array (
      0 => 'ilobjrolefoldergui',
      1 => 'ilobjobjecttemplateadministrationgui',
    ),
  ),
  'ilecsmappingsettingsgui' => 
  array (
    'cid' => 'ar',
    'class_name' => 'ilECSMappingSettingsGUI',
    'class_path' => './Services/WebServices/ECS/classes/Mapping/class.ilECSMappingSettingsGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilecssettingsgui',
    ),
  ),
  'ilecsparticipantsettingsgui' => 
  array (
    'cid' => 'at',
    'class_name' => 'ilECSParticipantSettingsGUI',
    'class_path' => './Services/WebServices/ECS/classes/class.ilECSParticipantSettingsGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilecssettingsgui',
    ),
  ),
  'ilecssettingsgui' => 
  array (
    'cid' => 'av',
    'class_name' => 'ilECSSettingsGUI',
    'class_path' => './Services/WebServices/ECS/classes/class.ilECSSettingsGUI.php',
    'children' => 
    array (
      0 => 'ilecsmappingsettingsgui',
      1 => 'ilecsparticipantsettingsgui',
    ),
    'parents' => 
    array (
      0 => 'ilobjecssettingsgui',
    ),
  ),
  'ilecsuserconsentmodalgui' => 
  array (
    'cid' => 'aw',
    'class_name' => 'ilECSUserConsentModalGUI',
    'class_path' => './Services/WebServices/ECS/classes/Consent/class.ilECSUserConsentModalGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjremotecategorygui',
      1 => 'ilobjremotecoursegui',
      2 => 'ilobjremotefilegui',
      3 => 'ilobjremoteglossarygui',
      4 => 'ilobjremotegroupgui',
      5 => 'ilobjremotelearningmodulegui',
      6 => 'ilobjremotetestgui',
      7 => 'ilobjremotewikigui',
    ),
  ),
  'ileditclipboardgui' => 
  array (
    'cid' => 'ay',
    'class_name' => 'ilEditClipboardGUI',
    'class_path' => './components/ILIAS/MediaPool/Clipboard/class.ilEditClipboardGUI.php',
    'children' => 
    array (
      0 => 'ilobjmediaobjectgui',
    ),
    'parents' => 
    array (
      0 => 'ilassgenfeedbackpagegui',
      1 => 'ilasshintpagegui',
      2 => 'ilassquestionpagegui',
      3 => 'ilassspecfeedbackpagegui',
      4 => 'ilblogpostinggui',
      5 => 'ilcontainerpagegui',
      6 => 'ilcontainerstartobjectspagegui',
      7 => 'ilcontentpagepagegui',
      8 => 'ildclcreateviewdefinitiongui',
      9 => 'ildcldetailedviewdefinitiongui',
      10 => 'ildcldetailedviewgui',
      11 => 'ildcleditviewdefinitiongui',
      12 => 'ilforumpagegui',
      13 => 'ilglossarydefpagegui',
      14 => 'ilimprintgui',
      15 => 'illmpagegui',
      16 => 'illopagegui',
      17 => 'illoginpagegui',
      18 => 'ilmediapoolpagegui',
      19 => 'ilobjlearningsequenceeditextrogui',
      20 => 'ilobjlearningsequenceeditintrogui',
      21 => 'ilobjmediapoolgui',
      22 => 'ilobjtestgui',
      23 => 'ilprgpageobjectgui',
      24 => 'ilpagelayoutgui',
      25 => 'ilpageobjectgui',
      26 => 'ilportfoliopagegui',
      27 => 'ilportfoliotemplatepagegui',
      28 => 'iltestexpresspageobjectgui',
      29 => 'iltestpagegui',
      30 => 'ilwikipagegui',
    ),
  ),
  'ilemployeetalkappointmentgui' => 
  array (
    'cid' => 'az',
    'class_name' => 'ilEmployeeTalkAppointmentGUI',
    'class_path' => './components/ILIAS/EmployeeTalk/classes/Talk/class.ilEmployeeTalkAppointmentGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjemployeetalkgui',
    ),
  ),
  'ilemployeetalkmystafflistgui' => 
  array (
    'cid' => 'b0',
    'class_name' => 'ilEmployeeTalkMyStaffListGUI',
    'class_path' => './components/ILIAS/EmployeeTalk/classes/Talk/class.ilEmployeeTalkMyStaffListGUI.php',
    'children' => 
    array (
      0 => 'ilobjemployeetalkgui',
      1 => 'ilobjemployeetalkseriesgui',
    ),
    'parents' => 
    array (
      0 => 'ilmystaffgui',
      1 => 'ilformpropertydispatchgui',
    ),
  ),
  'ilemployeetalkmystaffusergui' => 
  array (
    'cid' => 'b1',
    'class_name' => 'ilEmployeeTalkMyStaffUserGUI',
    'class_path' => './components/ILIAS/EmployeeTalk/classes/Talk/class.ilEmployeeTalkMyStaffUserGUI.php',
    'children' => 
    array (
      0 => 'ilobjemployeetalkgui',
      1 => 'ilobjemployeetalkseriesgui',
    ),
    'parents' => 
    array (
      0 => 'ilmstshowusergui',
      1 => 'ilformpropertydispatchgui',
    ),
  ),
  'ilexasstypewikiteamgui' => 
  array (
    'cid' => 'bb',
    'class_name' => 'ilExAssTypeWikiTeamGUI',
    'class_path' => './components/ILIAS/Exercise/Assignment/Types/GUI/classes/class.ilExAssTypeWikiTeamGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilexsubmissiongui',
    ),
  ),
  'ilexassignmenteditorgui' => 
  array (
    'cid' => 'bc',
    'class_name' => 'ilExAssignmentEditorGUI',
    'class_path' => './components/ILIAS/Exercise/Assignment/class.ilExAssignmentEditorGUI.php',
    'children' => 
    array (
      0 => 'ilexassignmentfilesystemgui',
      1 => 'ilexpeerreviewgui',
      2 => 'ilpropertyformgui',
      3 => 'ilresourcecollectiongui',
    ),
    'parents' => 
    array (
      0 => 'ilobjexercisegui',
    ),
  ),
  'ilexassignmentfilesystemgui' => 
  array (
    'cid' => 'bd',
    'class_name' => 'ilExAssignmentFileSystemGUI',
    'class_path' => './components/ILIAS/Exercise/Assignment/class.ilExAssignmentFileSystemGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilexassignmenteditorgui',
    ),
  ),
  'ilexpeerreviewgui' => 
  array (
    'cid' => 'bn',
    'class_name' => 'ilExPeerReviewGUI',
    'class_path' => './components/ILIAS/Exercise/PeerReview/class.ilExPeerReviewGUI.php',
    'children' => 
    array (
      0 => 'ilfilesystemgui',
      1 => 'ilratinggui',
      2 => 'ilexsubmissiontextgui',
      3 => 'ilinfoscreengui',
      4 => 'ilmessagegui',
    ),
    'parents' => 
    array (
      0 => 'ilexassignmenteditorgui',
      1 => 'ilexsubmissiongui',
      2 => 'ilexercisemanagementgui',
    ),
  ),
  'ilexsubmissionfilegui' => 
  array (
    'cid' => 'bp',
    'class_name' => 'ilExSubmissionFileGUI',
    'class_path' => './components/ILIAS/Exercise/Submission/class.ilExSubmissionFileGUI.php',
    'children' => 
    array (
      0 => 'ilrepostandarduploadhandlergui',
    ),
    'parents' => 
    array (
      0 => 'ilexsubmissiongui',
      1 => 'ilexercisemanagementgui',
    ),
  ),
  'ilexsubmissiongui' => 
  array (
    'cid' => 'bq',
    'class_name' => 'ilExSubmissionGUI',
    'class_path' => './components/ILIAS/Exercise/Submission/class.ilExSubmissionGUI.php',
    'children' => 
    array (
      0 => 'ilexsubmissionteamgui',
      1 => 'ilexsubmissionfilegui',
      2 => 'ilexsubmissiontextgui',
      3 => 'ilexsubmissionobjectgui',
      4 => 'ilexpeerreviewgui',
      5 => 'ilexasstypewikiteamgui',
    ),
    'parents' => 
    array (
      0 => 'ilassignmentpresentationgui',
    ),
  ),
  'ilexsubmissionobjectgui' => 
  array (
    'cid' => 'br',
    'class_name' => 'ilExSubmissionObjectGUI',
    'class_path' => './components/ILIAS/Exercise/Submission/class.ilExSubmissionObjectGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilexsubmissiongui',
    ),
  ),
  'ilexsubmissionteamgui' => 
  array (
    'cid' => 'bs',
    'class_name' => 'ilExSubmissionTeamGUI',
    'class_path' => './components/ILIAS/Exercise/Submission/class.ilExSubmissionTeamGUI.php',
    'children' => 
    array (
      0 => 'ilrepositorysearchgui',
    ),
    'parents' => 
    array (
      0 => 'ilexsubmissiongui',
      1 => 'ilexercisemanagementgui',
    ),
  ),
  'ilexsubmissiontextgui' => 
  array (
    'cid' => 'bt',
    'class_name' => 'ilExSubmissionTextGUI',
    'class_path' => './components/ILIAS/Exercise/Submission/class.ilExSubmissionTextGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilexpeerreviewgui',
      1 => 'ilexsubmissiongui',
      2 => 'ilexercisemanagementgui',
    ),
  ),
  'ilexccriteriacataloguegui' => 
  array (
    'cid' => 'bu',
    'class_name' => 'ilExcCriteriaCatalogueGUI',
    'class_path' => './components/ILIAS/Exercise/PeerReview/Criteria/class.ilExcCriteriaCatalogueGUI.php',
    'children' => 
    array (
      0 => 'ilexccriteriagui',
    ),
    'parents' => 
    array (
      0 => 'ilobjexercisegui',
    ),
  ),
  'ilexccriteriagui' => 
  array (
    'cid' => 'bw',
    'class_name' => 'ilExcCriteriaGUI',
    'class_path' => './components/ILIAS/Exercise/PeerReview/Criteria/class.ilExcCriteriaGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilexccriteriacataloguegui',
    ),
  ),
  'ilexcrandomassignmentgui' => 
  array (
    'cid' => 'bz',
    'class_name' => 'ilExcRandomAssignmentGUI',
    'class_path' => './components/ILIAS/Exercise/Assignment/Mandatory/class.ilExcRandomAssignmentGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjexercisegui',
    ),
  ),
  'ilexercisehandlergui' => 
  array (
    'cid' => 'c0',
    'class_name' => 'ilExerciseHandlerGUI',
    'class_path' => './components/ILIAS/Exercise/classes/class.ilExerciseHandlerGUI.php',
    'children' => 
    array (
      0 => 'ilobjexercisegui',
    ),
    'parents' => 
    array (
    ),
  ),
  'ilexercisemanagementgui' => 
  array (
    'cid' => 'c1',
    'class_name' => 'ilExerciseManagementGUI',
    'class_path' => './components/ILIAS/Exercise/classes/class.ilExerciseManagementGUI.php',
    'children' => 
    array (
      0 => 'ilfilesystemgui',
      1 => 'ilrepositorysearchgui',
      2 => 'ilexsubmissionteamgui',
      3 => 'ilexsubmissionfilegui',
      4 => 'ilexsubmissiontextgui',
      5 => 'ilexpeerreviewgui',
      6 => 'ilparticipantsperassignmenttablegui',
      7 => 'ilresourcecollectiongui',
    ),
    'parents' => 
    array (
      0 => 'ilobjexercisegui',
    ),
  ),
  'ilexportgui' => 
  array (
    'cid' => 'c6',
    'class_name' => 'ilExportGUI',
    'class_path' => './Services/Export/classes/class.ilExportGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjbibliographicgui',
      1 => 'ilobjbloggui',
      2 => 'ilobjcategorygui',
      3 => 'ilobjchatroomadmingui',
      4 => 'ilobjchatroomgui',
      5 => 'ilobjcontentobjectgui',
      6 => 'ilobjcontentpagegui',
      7 => 'ilobjcoursegui',
      8 => 'ilobjexercisegui',
      9 => 'ilobjfilebasedlmgui',
      10 => 'ilobjfilegui',
      11 => 'ilobjfoldergui',
      12 => 'ilobjforumgui',
      13 => 'ilobjglossarygui',
      14 => 'ilobjgroupgui',
      15 => 'ilobjindividualassessmentgui',
      16 => 'ilobjlearningmodulegui',
      17 => 'ilobjlearningsequencegui',
      18 => 'ilobjlinkresourcegui',
      19 => 'ilobjmediacastgui',
      20 => 'ilobjmediapoolgui',
      21 => 'ilobjpollgui',
      22 => 'ilobjportfoliotemplategui',
      23 => 'ilobjrolegui',
      24 => 'ilobjsahslearningmodulegui',
      25 => 'ilobjsessiongui',
      26 => 'ilobjskilltreegui',
      27 => 'ilobjstylesheetgui',
      28 => 'ilobjsurveygui',
      29 => 'ilobjwikigui',
      30 => 'ilsahseditgui',
    ),
  ),
  'ilextidgui' => 
  array (
    'cid' => 'ca',
    'class_name' => 'ilExtIdGUI',
    'class_path' => './components/ILIAS/OrgUnit/classes/ExtId/class.ilExtIdGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjorgunitgui',
    ),
  ),
  'ilfilesystemgui' => 
  array (
    'cid' => 'cf',
    'class_name' => 'ilFileSystemGUI',
    'class_path' => './Services/FileSystem/classes/class.ilFileSystemGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilexpeerreviewgui',
      1 => 'ilexercisemanagementgui',
      2 => 'ilobjfilebasedlmgui',
      3 => 'ilobjmediaobjectgui',
      4 => 'ilobjmediapoolgui',
      5 => 'ilobjsahslearningmodulegui',
      6 => 'ilobjscorm2004learningmodulegui',
      7 => 'ilobjscormlearningmodulegui',
      8 => 'ilsahseditgui',
    ),
  ),
  'ilfileversionsgui' => 
  array (
    'cid' => 'ci',
    'class_name' => 'ilFileVersionsGUI',
    'class_path' => './components/ILIAS/File/classes/Versions/class.ilFileVersionsGUI.php',
    'children' => 
    array (
      0 => 'ilwopiembeddedapplicationgui',
      1 => 'ilfileversionsuploadhandlergui',
    ),
    'parents' => 
    array (
      0 => 'ilobjfilegui',
    ),
  ),
  'ilfileversionsuploadhandlergui' => 
  array (
    'cid' => 'ck',
    'class_name' => 'ilFileVersionsUploadHandlerGUI',
    'class_path' => './components/ILIAS/File/classes/Versions/class.ilFileVersionsUploadHandlerGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilfileversionsgui',
    ),
  ),
  'ilformpropertydispatchgui' => 
  array (
    'cid' => 'co',
    'class_name' => 'ilFormPropertyDispatchGUI',
    'class_path' => './Services/Form/classes/class.ilFormPropertyDispatchGUI.php',
    'children' => 
    array (
      0 => 'ilchatroomauthinputgui',
      1 => 'ilemployeetalkmystafflistgui',
      2 => 'ilemployeetalkmystaffusergui',
      3 => 'illinkinputgui',
      4 => 'ilrepositoryselector2inputgui',
      5 => 'ilrepositoryselectorinputgui',
      6 => 'iltaxselectinputgui',
    ),
    'parents' => 
    array (
      0 => 'assclozetestgui',
      1 => 'asserrortextgui',
      2 => 'assfileuploadgui',
      3 => 'assformulaquestiongui',
      4 => 'assimagemapquestiongui',
      5 => 'asskprimchoicegui',
      6 => 'asslongmenugui',
      7 => 'assmatchingquestiongui',
      8 => 'assmultiplechoicegui',
      9 => 'assnumericgui',
      10 => 'assorderinghorizontalgui',
      11 => 'assorderingquestiongui',
      12 => 'asssinglechoicegui',
      13 => 'asstextquestiongui',
      14 => 'asstextsubsetgui',
      15 => 'ildclpropertyformgui',
      16 => 'illpobjectstatisticsadmintablegui',
      17 => 'illpobjectstatisticsdailytablegui',
      18 => 'illpobjectstatisticslptablegui',
      19 => 'illpobjectstatisticstablegui',
      20 => 'illpobjectstatisticstypestablegui',
      21 => 'illpprogresstablegui',
      22 => 'ilmdeditorgui',
      23 => 'ilmstlistcertificatesgui',
      24 => 'ilmstlistcoursestablegui',
      25 => 'ilmstshowusercoursestablegui',
      26 => 'ilobjemployeetalkserieslistgui',
      27 => 'ilobjstudyprogrammeautomembershipsgui',
      28 => 'ilobjstudyprogrammemembersgui',
      29 => 'ilobjtalktemplateadministrationlistgui',
      30 => 'ilparticipantsperassignmenttablegui',
      31 => 'ilpresentationlisttablegui',
      32 => 'ilpropertyformgui',
      33 => 'ilquestionbrowsertablegui',
      34 => 'ilrepositorysearchgui',
      35 => 'iltaxmdgui',
      36 => 'iltestpassdetailsoverviewtablegui',
      37 => 'iltestquestionbrowsertablegui',
      38 => 'iltestrandomquestionsetgeneralconfigformgui',
      39 => 'iltestrandomquestionsetpooldefinitionformgui',
      40 => 'iltrobjectuserspropstablegui',
      41 => 'iltrsummarytablegui',
      42 => 'iltruserobjectspropstablegui',
      43 => 'ilusertablegui',
    ),
  ),
  'ilforumexportgui' => 
  array (
    'cid' => 'cu',
    'class_name' => 'ilForumExportGUI',
    'class_path' => './components/ILIAS/Forum/classes/class.ilForumExportGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjforumgui',
    ),
  ),
  'ilforummoderatorsgui' => 
  array (
    'cid' => 'cv',
    'class_name' => 'ilForumModeratorsGUI',
    'class_path' => './components/ILIAS/Forum/classes/class.ilForumModeratorsGUI.php',
    'children' => 
    array (
      0 => 'ilrepositorysearchgui',
    ),
    'parents' => 
    array (
      0 => 'ilobjforumgui',
    ),
  ),
  'ilforumpagegui' => 
  array (
    'cid' => 'd0',
    'class_name' => 'ilForumPageGUI',
    'class_path' => './components/ILIAS/Forum/classes/CoPage/class.ilForumPageGUI.php',
    'children' => 
    array (
      0 => 'ilpageeditorgui',
      1 => 'ileditclipboardgui',
      2 => 'ilmdeditorgui',
      3 => 'ilpublicuserprofilegui',
      4 => 'ilnotegui',
      5 => 'ilpropertyformgui',
      6 => 'ilinternallinkgui',
      7 => 'ilpagemultilanggui',
    ),
    'parents' => 
    array (
      0 => 'ilobjforumgui',
    ),
  ),
  'ilforumsettingsgui' => 
  array (
    'cid' => 'd1',
    'class_name' => 'ilForumSettingsGUI',
    'class_path' => './components/ILIAS/Forum/classes/class.ilForumSettingsGUI.php',
    'children' => 
    array (
      0 => 'ilobjectcontentstylesettingsgui',
    ),
    'parents' => 
    array (
      0 => 'ilobjforumgui',
    ),
  ),
  'ilglobalunitconfigurationgui' => 
  array (
    'cid' => 'd9',
    'class_name' => 'ilGlobalUnitConfigurationGUI',
    'class_path' => './components/ILIAS/TestQuestionPool/classes/class.ilGlobalUnitConfigurationGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjassessmentfoldergui',
    ),
  ),
  'ilglossarydefpagegui' => 
  array (
    'cid' => 'db',
    'class_name' => 'ilGlossaryDefPageGUI',
    'class_path' => './components/ILIAS/Glossary/Definition/class.ilGlossaryDefPageGUI.php',
    'children' => 
    array (
      0 => 'ilpageeditorgui',
      1 => 'ileditclipboardgui',
      2 => 'ilobjectmetadatagui',
      3 => 'ilpublicuserprofilegui',
      4 => 'ilnotegui',
      5 => 'ilpropertyformgui',
      6 => 'ilinternallinkgui',
    ),
    'parents' => 
    array (
      0 => 'ilglossarypresentationgui',
      1 => 'ilglossarytermgui',
      2 => 'illmpresentationgui',
      3 => 'iltermdefinitioneditorgui',
    ),
  ),
  'ilglossaryeditorgui' => 
  array (
    'cid' => 'dc',
    'class_name' => 'ilGlossaryEditorGUI',
    'class_path' => './components/ILIAS/Glossary/Editing/class.ilGlossaryEditorGUI.php',
    'children' => 
    array (
      0 => 'ilobjglossarygui',
    ),
    'parents' => 
    array (
    ),
  ),
  'ilglossaryflashcardboxgui' => 
  array (
    'cid' => 'dd',
    'class_name' => 'ilGlossaryFlashcardBoxGUI',
    'class_path' => './components/ILIAS/Glossary/Flashcard/class.ilGlossaryFlashcardBoxGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilglossaryflashcardgui',
      1 => 'ilglossarypresentationgui',
    ),
  ),
  'ilglossaryflashcardgui' => 
  array (
    'cid' => 'de',
    'class_name' => 'ilGlossaryFlashcardGUI',
    'class_path' => './components/ILIAS/Glossary/Flashcard/class.ilGlossaryFlashcardGUI.php',
    'children' => 
    array (
      0 => 'ilglossaryflashcardboxgui',
    ),
    'parents' => 
    array (
      0 => 'ilglossarypresentationgui',
    ),
  ),
  'ilglossaryforeigntermcollectorgui' => 
  array (
    'cid' => 'df',
    'class_name' => 'ilGlossaryForeignTermCollectorGUI',
    'class_path' => './components/ILIAS/Glossary/Term/class.ilGlossaryForeignTermCollectorGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjglossarygui',
    ),
  ),
  'ilglossarypresentationgui' => 
  array (
    'cid' => 'di',
    'class_name' => 'ilGlossaryPresentationGUI',
    'class_path' => './components/ILIAS/Glossary/Presentation/class.ilGlossaryPresentationGUI.php',
    'children' => 
    array (
      0 => 'ilnotegui',
      1 => 'ilinfoscreengui',
      2 => 'ilpresentationlisttablegui',
      3 => 'ilglossarydefpagegui',
      4 => 'ilpresentationfullgui',
      5 => 'ilglossaryflashcardgui',
      6 => 'ilglossaryflashcardboxgui',
    ),
    'parents' => 
    array (
    ),
  ),
  'ilglossarytermgui' => 
  array (
    'cid' => 'dj',
    'class_name' => 'ilGlossaryTermGUI',
    'class_path' => './components/ILIAS/Glossary/Term/class.ilGlossaryTermGUI.php',
    'children' => 
    array (
      0 => 'iltermdefinitioneditorgui',
      1 => 'ilglossarydefpagegui',
      2 => 'ilpropertyformgui',
      3 => 'ilobjectmetadatagui',
    ),
    'parents' => 
    array (
      0 => 'ilobjglossarygui',
    ),
  ),
  'ilgroupaddtogroupactiongui' => 
  array (
    'cid' => 'dn',
    'class_name' => 'ilGroupAddToGroupActionGUI',
    'class_path' => './components/ILIAS/Group/UserActions/classes/class.ilGroupAddToGroupActionGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilgroupuseractionsgui',
    ),
  ),
  'ilgroupmembershipgui' => 
  array (
    'cid' => 'dp',
    'class_name' => 'ilGroupMembershipGUI',
    'class_path' => './components/ILIAS/Group/classes/class.ilGroupMembershipGUI.php',
    'children' => 
    array (
      0 => 'ilmailmembersearchgui',
      1 => 'ilusersgallerygui',
      2 => 'ilrepositorysearchgui',
      3 => 'ilcourseparticipantsgroupsgui',
      4 => 'ilobjectcustomuserfieldsgui',
      5 => 'ilsessionoverviewgui',
      6 => 'ilmemberexportgui',
    ),
    'parents' => 
    array (
      0 => 'ilobjgroupgui',
    ),
  ),
  'ilgroupregistrationgui' => 
  array (
    'cid' => 'dr',
    'class_name' => 'ilGroupRegistrationGUI',
    'class_path' => './components/ILIAS/Group/classes/class.ilGroupRegistrationGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjgroupgui',
    ),
  ),
  'ilgroupuseractionsgui' => 
  array (
    'cid' => 'ds',
    'class_name' => 'ilGroupUserActionsGUI',
    'class_path' => './components/ILIAS/Group/UserActions/classes/class.ilGroupUserActionsGUI.php',
    'children' => 
    array (
      0 => 'ilgroupaddtogroupactiongui',
    ),
    'parents' => 
    array (
      0 => 'ildashboardgui',
    ),
  ),
  'ilhtlmeditorgui' => 
  array (
    'cid' => 'du',
    'class_name' => 'ilHTLMEditorGUI',
    'class_path' => './components/ILIAS/HTMLLearningModule/classes/class.ilHTLMEditorGUI.php',
    'children' => 
    array (
      0 => 'ilobjfilebasedlmgui',
    ),
    'parents' => 
    array (
    ),
  ),
  'ilhtlmpresentationgui' => 
  array (
    'cid' => 'dv',
    'class_name' => 'ilHTLMPresentationGUI',
    'class_path' => './components/ILIAS/HTMLLearningModule/classes/class.ilHTLMPresentationGUI.php',
    'children' => 
    array (
      0 => 'ilobjfilebasedlmgui',
    ),
    'parents' => 
    array (
    ),
  ),
  'ilhelpgui' => 
  array (
    'cid' => 'dw',
    'class_name' => 'ilHelpGUI',
    'class_path' => './Services/Help/classes/class.ilHelpGUI.php',
    'children' => 
    array (
      0 => 'illmpagegui',
    ),
    'parents' => 
    array (
    ),
  ),
  'ilimagemapeditorgui' => 
  array (
    'cid' => 'e7',
    'class_name' => 'ilImageMapEditorGUI',
    'class_path' => './Services/MediaObjects/ImageMap/class.ilImageMapEditorGUI.php',
    'children' => 
    array (
      0 => 'ilinternallinkgui',
    ),
    'parents' => 
    array (
      0 => 'ilobjmediaobjectgui',
    ),
  ),
  'ilimprintgui' => 
  array (
    'cid' => 'ec',
    'class_name' => 'ilImprintGUI',
    'class_path' => './Services/Imprint/classes/class.ilImprintGUI.php',
    'children' => 
    array (
      0 => 'ilpageeditorgui',
      1 => 'ileditclipboardgui',
      2 => 'ilpublicuserprofilegui',
      3 => 'ilpageobjectgui',
    ),
    'parents' => 
    array (
      0 => 'ilobjlegalnoticegui',
    ),
  ),
  'ilindividualassessmentcommonsettingsgui' => 
  array (
    'cid' => 'ee',
    'class_name' => 'ilIndividualAssessmentCommonSettingsGUI',
    'class_path' => './components/ILIAS/IndividualAssessment/classes/Settings/class.ilIndividualAssessmentCommonSettingsGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilindividualassessmentsettingsgui',
    ),
  ),
  'ilindividualassessmentmembergui' => 
  array (
    'cid' => 'ef',
    'class_name' => 'ilIndividualAssessmentMemberGUI',
    'class_path' => './components/ILIAS/IndividualAssessment/classes/class.ilIndividualAssessmentMemberGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilindividualassessmentmembersgui',
    ),
  ),
  'ilindividualassessmentmembersgui' => 
  array (
    'cid' => 'eg',
    'class_name' => 'ilIndividualAssessmentMembersGUI',
    'class_path' => './components/ILIAS/IndividualAssessment/classes/class.ilIndividualAssessmentMembersGUI.php',
    'children' => 
    array (
      0 => 'ilrepositorysearchgui',
      1 => 'ilindividualassessmentmembergui',
    ),
    'parents' => 
    array (
      0 => 'ilobjindividualassessmentgui',
    ),
  ),
  'ilindividualassessmentsettingsgui' => 
  array (
    'cid' => 'ei',
    'class_name' => 'ilIndividualAssessmentSettingsGUI',
    'class_path' => './components/ILIAS/IndividualAssessment/classes/class.ilIndividualAssessmentSettingsGUI.php',
    'children' => 
    array (
      0 => 'ilindividualassessmentcommonsettingsgui',
    ),
    'parents' => 
    array (
      0 => 'ilobjindividualassessmentgui',
      1 => 'ilobjlearningsequencegui',
    ),
  ),
  'ilinfoscreengui' => 
  array (
    'cid' => 'ej',
    'class_name' => 'ilInfoScreenGUI',
    'class_path' => './Services/InfoScreen/classes/class.ilInfoScreenGUI.php',
    'children' => 
    array (
      0 => 'ilcommentgui',
      1 => 'ilcolumngui',
      2 => 'ilpublicuserprofilegui',
      3 => 'ilcommonactiondispatchergui',
    ),
    'parents' => 
    array (
      0 => 'ilcalendarappointmentpresentationgui',
      1 => 'ilexpeerreviewgui',
      2 => 'ilglossarypresentationgui',
      3 => 'illmpresentationgui',
      4 => 'ilobjbibliographicgui',
      5 => 'ilobjbloggui',
      6 => 'ilobjbookingpoolgui',
      7 => 'ilobjcategorygui',
      8 => 'ilobjcategoryreferencegui',
      9 => 'ilobjchatroomadmingui',
      10 => 'ilobjchatroomgui',
      11 => 'ilobjcloudgui',
      12 => 'ilobjcmixapigui',
      13 => 'ilobjcontentobjectgui',
      14 => 'ilobjcontentpagegui',
      15 => 'ilobjcoursegui',
      16 => 'ilobjcoursereferencegui',
      17 => 'ilobjdatacollectiongui',
      18 => 'ilobjemployeetalkgui',
      19 => 'ilobjemployeetalkseriesgui',
      20 => 'ilobjexercisegui',
      21 => 'ilobjfilebasedlmgui',
      22 => 'ilobjfilegui',
      23 => 'ilobjfoldergui',
      24 => 'ilobjforumgui',
      25 => 'ilobjglossarygui',
      26 => 'ilobjgroupgui',
      27 => 'ilobjgroupreferencegui',
      28 => 'ilobjindividualassessmentgui',
      29 => 'ilobjlticonsumergui',
      30 => 'ilobjlearningmodulegui',
      31 => 'ilobjlearningsequencegui',
      32 => 'ilobjlinkresourcegui',
      33 => 'ilobjmediacastgui',
      34 => 'ilobjmediapoolgui',
      35 => 'ilobjorgunitgui',
      36 => 'ilobjpollgui',
      37 => 'ilobjportfoliotemplategui',
      38 => 'ilobjquestionpoolgui',
      39 => 'ilobjremotecategorygui',
      40 => 'ilobjremotecoursegui',
      41 => 'ilobjremotefilegui',
      42 => 'ilobjremoteglossarygui',
      43 => 'ilobjremotegroupgui',
      44 => 'ilobjremotelearningmodulegui',
      45 => 'ilobjremotetestgui',
      46 => 'ilobjremotewikigui',
      47 => 'ilobjsahslearningmodulegui',
      48 => 'ilobjscorm2004learningmodulegui',
      49 => 'ilobjscormlearningmodulegui',
      50 => 'ilobjsessiongui',
      51 => 'ilobjstudyprogrammegui',
      52 => 'ilobjstudyprogrammereferencegui',
      53 => 'ilobjsurveygui',
      54 => 'ilobjsurveyquestionpoolgui',
      55 => 'ilobjtalktemplateadministrationgui',
      56 => 'ilobjtalktemplategui',
      57 => 'ilobjtestgui',
      58 => 'ilobjwikigui',
      59 => 'ilsahseditgui',
      60 => 'ilsahspresentationgui',
    ),
  ),
  'ilinternallinkgui' => 
  array (
    'cid' => 'el',
    'class_name' => 'ilInternalLinkGUI',
    'class_path' => './Services/Link/classes/class.ilInternalLinkGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilassgenfeedbackpagegui',
      1 => 'ilasshintpagegui',
      2 => 'ilassquestionpagegui',
      3 => 'ilassspecfeedbackpagegui',
      4 => 'ilcontainerpagegui',
      5 => 'ilcontainerstartobjectspagegui',
      6 => 'ilcontentpagepagegui',
      7 => 'ilforumpagegui',
      8 => 'ilglossarydefpagegui',
      9 => 'ilimagemapeditorgui',
      10 => 'illopagegui',
      11 => 'illinkinputgui',
      12 => 'illoginpagegui',
      13 => 'ilobjlinkresourcegui',
      14 => 'ilpciimtriggereditorgui',
      15 => 'ilpcimagemapeditorgui',
      16 => 'ilpageeditorgui',
      17 => 'ilpageobjectgui',
    ),
  ),
  'illdapsettingsgui' => 
  array (
    'cid' => 'eu',
    'class_name' => 'ilLDAPSettingsGUI',
    'class_path' => './Services/LDAP/classes/class.ilLDAPSettingsGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjauthsettingsgui',
    ),
  ),
  'illmeditshorttitlesgui' => 
  array (
    'cid' => 'ey',
    'class_name' => 'ilLMEditShortTitlesGUI',
    'class_path' => './components/ILIAS/LearningModule/classes/class.ilLMEditShortTitlesGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjcontentobjectgui',
      1 => 'ilobjlearningmodulegui',
    ),
  ),
  'illmeditorgui' => 
  array (
    'cid' => 'f1',
    'class_name' => 'ilLMEditorGUI',
    'class_path' => './components/ILIAS/LearningModule/Editing/class.ilLMEditorGUI.php',
    'children' => 
    array (
      0 => 'ilobjlearningmodulegui',
    ),
    'parents' => 
    array (
    ),
  ),
  'illmimportgui' => 
  array (
    'cid' => 'f4',
    'class_name' => 'ilLMImportGUI',
    'class_path' => './components/ILIAS/LearningModule/classes/class.ilLMImportGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjcontentobjectgui',
      1 => 'ilobjlearningmodulegui',
    ),
  ),
  'illmpagegui' => 
  array (
    'cid' => 'f9',
    'class_name' => 'ilLMPageGUI',
    'class_path' => './components/ILIAS/LearningModule/classes/class.ilLMPageGUI.php',
    'children' => 
    array (
      0 => 'ilpageeditorgui',
      1 => 'ilobjectmetadatagui',
      2 => 'ileditclipboardgui',
      3 => 'ilcommonactiondispatchergui',
      4 => 'ilpageobjectgui',
      5 => 'ilnewsitemgui',
      6 => 'ilquestioneditgui',
      7 => 'ilassquestionfeedbackeditinggui',
      8 => 'ilpagemultilanggui',
      9 => 'ilpropertyformgui',
    ),
    'parents' => 
    array (
      0 => 'ilhelpgui',
      1 => 'illmpageobjectgui',
      2 => 'illmpresentationgui',
    ),
  ),
  'illmpageobjectgui' => 
  array (
    'cid' => 'fa',
    'class_name' => 'ilLMPageObjectGUI',
    'class_path' => './components/ILIAS/LearningModule/classes/class.ilLMPageObjectGUI.php',
    'children' => 
    array (
      0 => 'illmpagegui',
      1 => 'ilassgenfeedbackpagegui',
    ),
    'parents' => 
    array (
      0 => 'ilobjcontentobjectgui',
      1 => 'ilobjlearningmodulegui',
    ),
  ),
  'illmpresentationgui' => 
  array (
    'cid' => 'fc',
    'class_name' => 'ilLMPresentationGUI',
    'class_path' => './components/ILIAS/LearningModule/Presentation/class.ilLMPresentationGUI.php',
    'children' => 
    array (
      0 => 'ilcommentgui',
      1 => 'ilinfoscreengui',
      2 => 'illmpagegui',
      3 => 'ilglossarydefpagegui',
      4 => 'ilcommonactiondispatchergui',
      5 => 'illearningprogressgui',
      6 => 'ilassgenfeedbackpagegui',
      7 => 'ilratinggui',
    ),
    'parents' => 
    array (
    ),
  ),
  'illoeditorgui' => 
  array (
    'cid' => 'fh',
    'class_name' => 'ilLOEditorGUI',
    'class_path' => './components/ILIAS/Course/classes/Objectives/class.ilLOEditorGUI.php',
    'children' => 
    array (
      0 => 'ilcourseobjectivesgui',
      1 => 'ilcontainerstartobjectsgui',
      2 => 'ilconditionhandlergui',
      3 => 'illopagegui',
    ),
    'parents' => 
    array (
      0 => 'ilobjcoursegui',
    ),
  ),
  'illomembertestresultgui' => 
  array (
    'cid' => 'fi',
    'class_name' => 'ilLOMemberTestResultGUI',
    'class_path' => './components/ILIAS/Course/classes/Objectives/class.ilLOMemberTestResultGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjcoursegui',
    ),
  ),
  'illopagegui' => 
  array (
    'cid' => 'fk',
    'class_name' => 'ilLOPageGUI',
    'class_path' => './components/ILIAS/Course/classes/Objectives/class.ilLOPageGUI.php',
    'children' => 
    array (
      0 => 'ilpageeditorgui',
      1 => 'ileditclipboardgui',
      2 => 'ilmdeditorgui',
      3 => 'ilpublicuserprofilegui',
      4 => 'ilnotegui',
      5 => 'ilpropertyformgui',
      6 => 'ilinternallinkgui',
      7 => 'ilpagemultilanggui',
    ),
    'parents' => 
    array (
      0 => 'illoeditorgui',
      1 => 'ilobjcoursegui',
    ),
  ),
  'illplistofobjectsgui' => 
  array (
    'cid' => 'fn',
    'class_name' => 'ilLPListOfObjectsGUI',
    'class_path' => './Services/Tracking/classes/repository_statistics/class.ilLPListOfObjectsGUI.php',
    'children' => 
    array (
      0 => 'iluserfiltergui',
      1 => 'iltruserobjectspropstablegui',
      2 => 'iltrsummarytablegui',
      3 => 'iltrobjectuserspropstablegui',
      4 => 'iltrmatrixtablegui',
    ),
    'parents' => 
    array (
      0 => 'illearningprogressgui',
    ),
  ),
  'illplistofprogressgui' => 
  array (
    'cid' => 'fo',
    'class_name' => 'ilLPListOfProgressGUI',
    'class_path' => './Services/Tracking/classes/repository_statistics/class.ilLPListOfProgressGUI.php',
    'children' => 
    array (
      0 => 'illpprogresstablegui',
    ),
    'parents' => 
    array (
      0 => 'illearningprogressgui',
    ),
  ),
  'illplistofsettingsgui' => 
  array (
    'cid' => 'fp',
    'class_name' => 'ilLPListOfSettingsGUI',
    'class_path' => './Services/Tracking/classes/repository_statistics/class.ilLPListOfSettingsGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'illearningprogressgui',
    ),
  ),
  'illpobjectstatisticsadmintablegui' => 
  array (
    'cid' => 'fq',
    'class_name' => 'ilLPObjectStatisticsAdminTableGUI',
    'class_path' => './Services/Tracking/classes/object_statistics/class.ilLPObjectStatisticsAdminTableGUI.php',
    'children' => 
    array (
      0 => 'ilformpropertydispatchgui',
    ),
    'parents' => 
    array (
    ),
  ),
  'illpobjectstatisticsdailytablegui' => 
  array (
    'cid' => 'fr',
    'class_name' => 'ilLPObjectStatisticsDailyTableGUI',
    'class_path' => './Services/Tracking/classes/object_statistics/class.ilLPObjectStatisticsDailyTableGUI.php',
    'children' => 
    array (
      0 => 'ilformpropertydispatchgui',
    ),
    'parents' => 
    array (
      0 => 'illpobjectstatisticsgui',
    ),
  ),
  'illpobjectstatisticsgui' => 
  array (
    'cid' => 'fs',
    'class_name' => 'ilLPObjectStatisticsGUI',
    'class_path' => './Services/Tracking/classes/object_statistics/class.ilLPObjectStatisticsGUI.php',
    'children' => 
    array (
      0 => 'illpobjectstatisticstablegui',
      1 => 'illpobjectstatisticsdailytablegui',
      2 => 'illpobjectstatisticslptablegui',
    ),
    'parents' => 
    array (
      0 => 'illearningprogressgui',
      1 => 'ilobjusertrackinggui',
    ),
  ),
  'illpobjectstatisticslptablegui' => 
  array (
    'cid' => 'ft',
    'class_name' => 'ilLPObjectStatisticsLPTableGUI',
    'class_path' => './Services/Tracking/classes/object_statistics/class.ilLPObjectStatisticsLPTableGUI.php',
    'children' => 
    array (
      0 => 'ilformpropertydispatchgui',
    ),
    'parents' => 
    array (
      0 => 'illpobjectstatisticsgui',
    ),
  ),
  'illpobjectstatisticstablegui' => 
  array (
    'cid' => 'fu',
    'class_name' => 'ilLPObjectStatisticsTableGUI',
    'class_path' => './Services/Tracking/classes/object_statistics/class.ilLPObjectStatisticsTableGUI.php',
    'children' => 
    array (
      0 => 'ilformpropertydispatchgui',
    ),
    'parents' => 
    array (
      0 => 'illpobjectstatisticsgui',
    ),
  ),
  'illpobjectstatisticstypestablegui' => 
  array (
    'cid' => 'fv',
    'class_name' => 'ilLPObjectStatisticsTypesTableGUI',
    'class_path' => './Services/Tracking/classes/object_statistics/class.ilLPObjectStatisticsTypesTableGUI.php',
    'children' => 
    array (
      0 => 'ilformpropertydispatchgui',
    ),
    'parents' => 
    array (
    ),
  ),
  'illpprogresstablegui' => 
  array (
    'cid' => 'fw',
    'class_name' => 'ilLPProgressTableGUI',
    'class_path' => './Services/Tracking/classes/repository_statistics/class.ilLPProgressTableGUI.php',
    'children' => 
    array (
      0 => 'ilformpropertydispatchgui',
    ),
    'parents' => 
    array (
      0 => 'illplistofprogressgui',
    ),
  ),
  'illticonsumeprovidersettingsgui' => 
  array (
    'cid' => 'g1',
    'class_name' => 'ilLTIConsumeProviderSettingsGUI',
    'class_path' => './components/ILIAS/LTIConsumer/classes/class.ilLTIConsumeProviderSettingsGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'illticonsumersettingsgui',
    ),
  ),
  'illticonsumeradministrationgui' => 
  array (
    'cid' => 'g2',
    'class_name' => 'ilLTIConsumerAdministrationGUI',
    'class_path' => './components/ILIAS/LTIConsumer/classes/class.ilLTIConsumerAdministrationGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjltiadministrationgui',
    ),
  ),
  'illticonsumercontentgui' => 
  array (
    'cid' => 'g3',
    'class_name' => 'ilLTIConsumerContentGUI',
    'class_path' => './components/ILIAS/LTIConsumer/classes/class.ilLTIConsumerContentGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjlticonsumergui',
    ),
  ),
  'illticonsumergradesynchronizationgui' => 
  array (
    'cid' => 'g4',
    'class_name' => 'ilLTIConsumerGradeSynchronizationGUI',
    'class_path' => './components/ILIAS/LTIConsumer/classes/class.ilLTIConsumerGradeSynchronizationGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjlticonsumergui',
    ),
  ),
  'illticonsumerscoringgui' => 
  array (
    'cid' => 'g9',
    'class_name' => 'ilLTIConsumerScoringGUI',
    'class_path' => './components/ILIAS/LTIConsumer/classes/class.ilLTIConsumerScoringGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjlticonsumergui',
    ),
  ),
  'illticonsumersettingsgui' => 
  array (
    'cid' => 'gc',
    'class_name' => 'ilLTIConsumerSettingsGUI',
    'class_path' => './components/ILIAS/LTIConsumer/classes/class.ilLTIConsumerSettingsGUI.php',
    'children' => 
    array (
      0 => 'illticonsumeprovidersettingsgui',
      1 => 'ilcertificategui',
    ),
    'parents' => 
    array (
      0 => 'ilobjlticonsumergui',
    ),
  ),
  'illticonsumerxapistatementsgui' => 
  array (
    'cid' => 'ge',
    'class_name' => 'ilLTIConsumerXapiStatementsGUI',
    'class_path' => './components/ILIAS/LTIConsumer/classes/class.ilLTIConsumerXapiStatementsGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjlticonsumergui',
    ),
  ),
  'illtiproviderobjectsettinggui' => 
  array (
    'cid' => 'gf',
    'class_name' => 'ilLTIProviderObjectSettingGUI',
    'class_path' => './Services/LTI/classes/InternalProvider/class.ilLTIProviderObjectSettingGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjcontentobjectgui',
      1 => 'ilobjcoursegui',
      2 => 'ilobjgroupgui',
      3 => 'ilobjlearningmodulegui',
      4 => 'ilobjscorm2004learningmodulegui',
      5 => 'ilobjscormlearningmodulegui',
      6 => 'ilobjsurveygui',
      7 => 'ilobjtestgui',
      8 => 'ilobjwikigui',
      9 => 'ilsahseditgui',
    ),
  ),
  'illtiroutergui' => 
  array (
    'cid' => 'gh',
    'class_name' => 'ilLTIRouterGUI',
    'class_path' => './Services/LTI/classes/class.ilLTIRouterGUI.php',
    'children' => 
    array (
      0 => 'illtiviewgui',
    ),
    'parents' => 
    array (
    ),
  ),
  'illtiviewgui' => 
  array (
    'cid' => 'gi',
    'class_name' => 'ilLTIViewGUI',
    'class_path' => './Services/LTI/classes/class.ilLTIViewGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'illtiroutergui',
    ),
  ),
  'illearninghistorygui' => 
  array (
    'cid' => 'gl',
    'class_name' => 'ilLearningHistoryGUI',
    'class_path' => './Services/LearningHistory/classes/class.ilLearningHistoryGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilachievementsgui',
      1 => 'ilpageobjectgui',
      2 => 'ilportfoliopagegui',
    ),
  ),
  'illearningprogressgui' => 
  array (
    'cid' => 'go',
    'class_name' => 'ilLearningProgressGUI',
    'class_path' => './Services/Tracking/classes/class.ilLearningProgressGUI.php',
    'children' => 
    array (
      0 => 'illplistofobjectsgui',
      1 => 'illplistofsettingsgui',
      2 => 'illplistofprogressgui',
      3 => 'illpobjectstatisticsgui',
    ),
    'parents' => 
    array (
      0 => 'ilachievementsgui',
      1 => 'illmpresentationgui',
      2 => 'ilobjcmixapigui',
      3 => 'ilobjcontentobjectgui',
      4 => 'ilobjcontentpagegui',
      5 => 'ilobjcoursegui',
      6 => 'ilobjcoursereferencegui',
      7 => 'ilobjexercisegui',
      8 => 'ilobjfilebasedlmgui',
      9 => 'ilobjfilegui',
      10 => 'ilobjfoldergui',
      11 => 'ilobjforumgui',
      12 => 'ilobjgroupgui',
      13 => 'ilobjindividualassessmentgui',
      14 => 'ilobjlticonsumergui',
      15 => 'ilobjlearningmodulegui',
      16 => 'ilobjlearningsequencegui',
      17 => 'ilobjmediacastgui',
      18 => 'ilobjorgunitgui',
      19 => 'ilobjsahslearningmodulegui',
      20 => 'ilobjscorm2004learningmodulegui',
      21 => 'ilobjscormlearningmodulegui',
      22 => 'ilobjsessiongui',
      23 => 'ilobjsurveygui',
      24 => 'ilobjtestgui',
      25 => 'ilobjusergui',
      26 => 'ilobjusertrackinggui',
      27 => 'ilsahspresentationgui',
    ),
  ),
  'illearningsequencemembershipgui' => 
  array (
    'cid' => 'gq',
    'class_name' => 'ilLearningSequenceMembershipGUI',
    'class_path' => './components/ILIAS/LearningSequence/classes/Members/class.ilLearningSequenceMembershipGUI.php',
    'children' => 
    array (
      0 => 'ilmailmembersearchgui',
      1 => 'ilusersgallerygui',
      2 => 'ilrepositorysearchgui',
      3 => 'ilcourseparticipantsgroupsgui',
      4 => 'ilobjectcustomuserfieldsgui',
      5 => 'ilsessionoverviewgui',
      6 => 'ilmemberexportgui',
    ),
    'parents' => 
    array (
      0 => 'ilobjlearningsequencegui',
    ),
  ),
  'illegaldocumentsadministrationgui' => 
  array (
    'cid' => 'gt',
    'class_name' => 'ilLegalDocumentsAdministrationGUI',
    'class_path' => './Services/LegalDocuments/classes/class.ilLegalDocumentsAdministrationGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjdataprotectiongui',
      1 => 'ilobjtermsofservicegui',
    ),
  ),
  'illegaldocumentsagreementgui' => 
  array (
    'cid' => 'gu',
    'class_name' => 'ilLegalDocumentsAgreementGUI',
    'class_path' => './Services/LegalDocuments/classes/class.ilLegalDocumentsAgreementGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilpersonalprofilegui',
    ),
  ),
  'illegaldocumentswithdrawalgui' => 
  array (
    'cid' => 'gv',
    'class_name' => 'ilLegalDocumentsWithdrawalGUI',
    'class_path' => './Services/LegalDocuments/classes/class.ilLegalDocumentsWithdrawalGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilpersonalprofilegui',
    ),
  ),
  'illikegui' => 
  array (
    'cid' => 'gx',
    'class_name' => 'ilLikeGUI',
    'class_path' => './Services/Like/classes/class.ilLikeGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilnewstimelinegui',
    ),
  ),
  'illinkinputgui' => 
  array (
    'cid' => 'gy',
    'class_name' => 'ilLinkInputGUI',
    'class_path' => './Services/Form/classes/class.ilLinkInputGUI.php',
    'children' => 
    array (
      0 => 'ilinternallinkgui',
    ),
    'parents' => 
    array (
      0 => 'ilformpropertydispatchgui',
    ),
  ),
  'illinkresourcehandlergui' => 
  array (
    'cid' => 'gz',
    'class_name' => 'ilLinkResourceHandlerGUI',
    'class_path' => './components/ILIAS/WebResource/classes/class.ilLinkResourceHandlerGUI.php',
    'children' => 
    array (
      0 => 'ilobjlinkresourcegui',
    ),
    'parents' => 
    array (
    ),
  ),
  'illocalunitconfigurationgui' => 
  array (
    'cid' => 'h4',
    'class_name' => 'ilLocalUnitConfigurationGUI',
    'class_path' => './components/ILIAS/TestQuestionPool/classes/class.ilLocalUnitConfigurationGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjquestionpoolgui',
      1 => 'ilobjtestgui',
    ),
  ),
  'illocalusergui' => 
  array (
    'cid' => 'h5',
    'class_name' => 'ilLocalUserGUI',
    'class_path' => './components/ILIAS/OrgUnit/classes/LocalUser/class.ilLocalUserGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjorgunitgui',
    ),
  ),
  'illoginpagegui' => 
  array (
    'cid' => 'ha',
    'class_name' => 'ilLoginPageGUI',
    'class_path' => './Services/Authentication/classes/class.ilLoginPageGUI.php',
    'children' => 
    array (
      0 => 'ilpageeditorgui',
      1 => 'ileditclipboardgui',
      2 => 'ilmdeditorgui',
      3 => 'ilpublicuserprofilegui',
      4 => 'ilnotegui',
      5 => 'ilpropertyformgui',
      6 => 'ilinternallinkgui',
    ),
    'parents' => 
    array (
      0 => 'ilauthloginpageeditorgui',
      1 => 'ilstartupgui',
    ),
  ),
  'illuceneadvancedsearchgui' => 
  array (
    'cid' => 'hc',
    'class_name' => 'ilLuceneAdvancedSearchGUI',
    'class_path' => './Services/Search/classes/Lucene/class.ilLuceneAdvancedSearchGUI.php',
    'children' => 
    array (
      0 => 'ilobjectgui',
      1 => 'ilcontainergui',
      2 => 'ilobjcategorygui',
      3 => 'ilobjcoursegui',
      4 => 'ilobjfoldergui',
      5 => 'ilobjgroupgui',
      6 => 'ilobjrootfoldergui',
      7 => 'ilobjectcopygui',
    ),
    'parents' => 
    array (
      0 => 'ilsearchcontrollergui',
    ),
  ),
  'illucenesearchgui' => 
  array (
    'cid' => 'he',
    'class_name' => 'ilLuceneSearchGUI',
    'class_path' => './Services/Search/classes/Lucene/class.ilLuceneSearchGUI.php',
    'children' => 
    array (
      0 => 'ilpropertyformgui',
      1 => 'ilobjectgui',
      2 => 'ilcontainergui',
      3 => 'ilobjcategorygui',
      4 => 'ilobjcoursegui',
      5 => 'ilobjfoldergui',
      6 => 'ilobjgroupgui',
      7 => 'ilobjrootfoldergui',
      8 => 'ilobjectcopygui',
    ),
    'parents' => 
    array (
      0 => 'ilsearchcontrollergui',
    ),
  ),
  'illuceneusersearchgui' => 
  array (
    'cid' => 'hf',
    'class_name' => 'ilLuceneUserSearchGUI',
    'class_path' => './Services/Search/classes/Lucene/class.ilLuceneUserSearchGUI.php',
    'children' => 
    array (
      0 => 'ilpublicuserprofilegui',
    ),
    'parents' => 
    array (
      0 => 'ilsearchcontrollergui',
    ),
  ),
  'ilmdcopyrightimageuploadhandlergui' => 
  array (
    'cid' => 'hg',
    'class_name' => 'ilMDCopyrightImageUploadHandlerGUI',
    'class_path' => './Services/MetaData/classes/Settings/Copyright/class.ilMDCopyrightImageUploadHandlerGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilmdcopyrightselectiongui',
    ),
  ),
  'ilmdcopyrightselectiongui' => 
  array (
    'cid' => 'hh',
    'class_name' => 'ilMDCopyrightSelectionGUI',
    'class_path' => './Services/MetaData/classes/Settings/Copyright/class.ilMDCopyrightSelectionGUI.php',
    'children' => 
    array (
      0 => 'ilmdcopyrightusagegui',
      1 => 'ilmdcopyrightimageuploadhandlergui',
    ),
    'parents' => 
    array (
      0 => 'ilobjmdsettingsgui',
    ),
  ),
  'ilmdcopyrightusagegui' => 
  array (
    'cid' => 'hj',
    'class_name' => 'ilMDCopyrightUsageGUI',
    'class_path' => './Services/MetaData/classes/Settings/Copyright/Usage/class.ilMDCopyrightUsageGUI.php',
    'children' => 
    array (
      0 => 'ilpublicuserprofilegui',
    ),
    'parents' => 
    array (
      0 => 'ilmdcopyrightselectiongui',
    ),
  ),
  'ilmdeditorgui' => 
  array (
    'cid' => 'hl',
    'class_name' => 'ilMDEditorGUI',
    'class_path' => './Services/MetaData/classes/Editor/class.ilMDEditorGUI.php',
    'children' => 
    array (
      0 => 'ilformpropertydispatchgui',
    ),
    'parents' => 
    array (
      0 => 'ilassgenfeedbackpagegui',
      1 => 'ilasshintpagegui',
      2 => 'ilassquestionpagegui',
      3 => 'ilassspecfeedbackpagegui',
      4 => 'ilcontainerpagegui',
      5 => 'ilcontainerstartobjectspagegui',
      6 => 'ilcontentpagepagegui',
      7 => 'ilforumpagegui',
      8 => 'illopagegui',
      9 => 'illoginpagegui',
      10 => 'ilobjchatroomadmingui',
      11 => 'ilobjchatroomgui',
      12 => 'ilobjcontentpagegui',
      13 => 'ilobjglossarygui',
      14 => 'ilobjectmetadatagui',
      15 => 'iltestpagegui',
    ),
  ),
  'ilmmitemtranslationgui' => 
  array (
    'cid' => 'hn',
    'class_name' => 'ilMMItemTranslationGUI',
    'class_path' => './Services/MainMenu/classes/Administration/class.ilMMItemTranslationGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilmmsubitemgui',
      1 => 'ilmmtopitemgui',
    ),
  ),
  'ilmmsubitemgui' => 
  array (
    'cid' => 'hp',
    'class_name' => 'ilMMSubItemGUI',
    'class_path' => './Services/MainMenu/classes/Administration/class.ilMMSubItemGUI.php',
    'children' => 
    array (
      0 => 'ilmmitemtranslationgui',
    ),
    'parents' => 
    array (
      0 => 'ilobjmainmenugui',
    ),
  ),
  'ilmmtopitemgui' => 
  array (
    'cid' => 'ht',
    'class_name' => 'ilMMTopItemGUI',
    'class_path' => './Services/MainMenu/classes/Administration/class.ilMMTopItemGUI.php',
    'children' => 
    array (
      0 => 'ilmmitemtranslationgui',
    ),
    'parents' => 
    array (
      0 => 'ilobjmainmenugui',
    ),
  ),
  'ilmmuploadhandlergui' => 
  array (
    'cid' => 'hv',
    'class_name' => 'ilMMUploadHandlerGUI',
    'class_path' => './Services/MainMenu/classes/Administration/class.ilMMUploadHandlerGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjmainmenugui',
    ),
  ),
  'ilmstlistcertificatesgui' => 
  array (
    'cid' => 'hw',
    'class_name' => 'ilMStListCertificatesGUI',
    'class_path' => './Services/MyStaff/classes/ListCertificates/class.ilMStListCertificatesGUI.php',
    'children' => 
    array (
      0 => 'ilformpropertydispatchgui',
      1 => 'ilusercertificateapigui',
    ),
    'parents' => 
    array (
      0 => 'ilmystaffgui',
    ),
  ),
  'ilmstlistcompetencesgui' => 
  array (
    'cid' => 'hx',
    'class_name' => 'ilMStListCompetencesGUI',
    'class_path' => './Services/MyStaff/classes/ListCompetences/class.ilMStListCompetencesGUI.php',
    'children' => 
    array (
      0 => 'ilmstlistcompetencesskillsgui',
    ),
    'parents' => 
    array (
      0 => 'ilmystaffgui',
    ),
  ),
  'ilmstlistcompetencesskillsgui' => 
  array (
    'cid' => 'hy',
    'class_name' => 'ilMStListCompetencesSkillsGUI',
    'class_path' => './Services/MyStaff/classes/ListCompetences/Skills/class.ilMStListCompetencesSkillsGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilmstlistcompetencesgui',
    ),
  ),
  'ilmstlistcoursesgui' => 
  array (
    'cid' => 'hz',
    'class_name' => 'ilMStListCoursesGUI',
    'class_path' => './Services/MyStaff/classes/ListCourses/class.ilMStListCoursesGUI.php',
    'children' => 
    array (
      0 => 'ilmstlistcoursestablegui',
    ),
    'parents' => 
    array (
      0 => 'ilmystaffgui',
    ),
  ),
  'ilmstlistcoursestablegui' => 
  array (
    'cid' => 'i0',
    'class_name' => 'ilMStListCoursesTableGUI',
    'class_path' => './Services/MyStaff/classes/ListCourses/class.ilMStListCoursesTableGUI.php',
    'children' => 
    array (
      0 => 'ilformpropertydispatchgui',
    ),
    'parents' => 
    array (
      0 => 'ilmstlistcoursesgui',
    ),
  ),
  'ilmstlistusersgui' => 
  array (
    'cid' => 'i1',
    'class_name' => 'ilMStListUsersGUI',
    'class_path' => './Services/MyStaff/classes/ListUsers/class.ilMStListUsersGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilmystaffgui',
    ),
  ),
  'ilmstshowusercompetencesgui' => 
  array (
    'cid' => 'i2',
    'class_name' => 'ilMStShowUserCompetencesGUI',
    'class_path' => './Services/MyStaff/classes/ShowUser/Competences/class.ilMStShowUserCompetencesGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilmstshowusergui',
    ),
  ),
  'ilmstshowusercoursesgui' => 
  array (
    'cid' => 'i3',
    'class_name' => 'ilMStShowUserCoursesGUI',
    'class_path' => './Services/MyStaff/classes/ShowUser/Courses/class.ilMStShowUserCoursesGUI.php',
    'children' => 
    array (
      0 => 'ilmstshowusercoursestablegui',
    ),
    'parents' => 
    array (
      0 => 'ilmstshowusergui',
    ),
  ),
  'ilmstshowusercoursestablegui' => 
  array (
    'cid' => 'i4',
    'class_name' => 'ilMStShowUserCoursesTableGUI',
    'class_path' => './Services/MyStaff/classes/ShowUser/Courses/class.ilMStShowUserCoursesTableGUI.php',
    'children' => 
    array (
      0 => 'ilformpropertydispatchgui',
    ),
    'parents' => 
    array (
      0 => 'ilmstshowusercoursesgui',
    ),
  ),
  'ilmstshowusergui' => 
  array (
    'cid' => 'i5',
    'class_name' => 'ilMStShowUserGUI',
    'class_path' => './Services/MyStaff/classes/ShowUser/class.ilMStShowUserGUI.php',
    'children' => 
    array (
      0 => 'ilusercertificategui',
      1 => 'ilemployeetalkmystaffusergui',
      2 => 'ilmstshowusercompetencesgui',
      3 => 'ilmstshowusercoursesgui',
    ),
    'parents' => 
    array (
      0 => 'ilmystaffgui',
    ),
  ),
  'ilmailattachmentgui' => 
  array (
    'cid' => 'i6',
    'class_name' => 'ilMailAttachmentGUI',
    'class_path' => './Services/Mail/classes/class.ilMailAttachmentGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilmailformgui',
      1 => 'ilmailgui',
    ),
  ),
  'ilmailfoldergui' => 
  array (
    'cid' => 'i8',
    'class_name' => 'ilMailFolderGUI',
    'class_path' => './Services/Mail/classes/class.ilMailFolderGUI.php',
    'children' => 
    array (
      0 => 'ilpublicuserprofilegui',
    ),
    'parents' => 
    array (
      0 => 'ilmailgui',
    ),
  ),
  'ilmailformgui' => 
  array (
    'cid' => 'ib',
    'class_name' => 'ilMailFormGUI',
    'class_path' => './Services/Mail/classes/class.ilMailFormGUI.php',
    'children' => 
    array (
      0 => 'ilmailattachmentgui',
      1 => 'ilmailsearchgui',
      2 => 'ilmailsearchcoursesgui',
      3 => 'ilmailsearchgroupsgui',
      4 => 'ilmailinglistsgui',
    ),
    'parents' => 
    array (
      0 => 'ilmailgui',
    ),
  ),
  'ilmailgui' => 
  array (
    'cid' => 'ic',
    'class_name' => 'ilMailGUI',
    'class_path' => './Services/Mail/classes/class.ilMailGUI.php',
    'children' => 
    array (
      0 => 'ilmailfoldergui',
      1 => 'ilmailformgui',
      2 => 'ilcontactgui',
      3 => 'ilmailoptionsgui',
      4 => 'ilmailattachmentgui',
      5 => 'ilmailsearchgui',
      6 => 'ilobjusergui',
    ),
    'parents' => 
    array (
    ),
  ),
  'ilmailmembersearchgui' => 
  array (
    'cid' => 'id',
    'class_name' => 'ilMailMemberSearchGUI',
    'class_path' => './Services/Contact/classes/class.ilMailMemberSearchGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilcoursemembershipgui',
      1 => 'ilgroupmembershipgui',
      2 => 'illearningsequencemembershipgui',
      3 => 'ilobjcoursegui',
      4 => 'ilobjgroupgui',
      5 => 'ilsessionmembershipgui',
    ),
  ),
  'ilmailoptionsgui' => 
  array (
    'cid' => 'ig',
    'class_name' => 'ilMailOptionsGUI',
    'class_path' => './Services/Mail/classes/class.ilMailOptionsGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilmailgui',
      1 => 'ilpersonalsettingsgui',
    ),
  ),
  'ilmailsearchcoursesgui' => 
  array (
    'cid' => 'ii',
    'class_name' => 'ilMailSearchCoursesGUI',
    'class_path' => './Services/Contact/classes/class.ilMailSearchCoursesGUI.php',
    'children' => 
    array (
      0 => 'ilbuddysystemgui',
    ),
    'parents' => 
    array (
      0 => 'ilcontactgui',
      1 => 'ilmailformgui',
      2 => 'ilworkspaceaccessgui',
    ),
  ),
  'ilmailsearchgui' => 
  array (
    'cid' => 'ij',
    'class_name' => 'ilMailSearchGUI',
    'class_path' => './Services/Contact/classes/class.ilMailSearchGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ildashboardgui',
      1 => 'ilmailformgui',
      2 => 'ilmailgui',
      3 => 'ilworkspaceaccessgui',
    ),
  ),
  'ilmailsearchgroupsgui' => 
  array (
    'cid' => 'ik',
    'class_name' => 'ilMailSearchGroupsGUI',
    'class_path' => './Services/Contact/classes/class.ilMailSearchGroupsGUI.php',
    'children' => 
    array (
      0 => 'ilbuddysystemgui',
    ),
    'parents' => 
    array (
      0 => 'ilcontactgui',
      1 => 'ilmailformgui',
      2 => 'ilworkspaceaccessgui',
    ),
  ),
  'ilmailtemplategui' => 
  array (
    'cid' => 'ip',
    'class_name' => 'ilMailTemplateGUI',
    'class_path' => './Services/Mail/classes/class.ilMailTemplateGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjmailgui',
    ),
  ),
  'ilmailinglistsgui' => 
  array (
    'cid' => 'is',
    'class_name' => 'ilMailingListsGUI',
    'class_path' => './Services/Contact/classes/class.ilMailingListsGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilcontactgui',
      1 => 'ilmailformgui',
    ),
  ),
  'ilmarkschemagui' => 
  array (
    'cid' => 'iy',
    'class_name' => 'ilMarkSchemaGUI',
    'class_path' => './components/ILIAS/Test/classes/class.ilMarkSchemaGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjtestgui',
    ),
  ),
  'ilmathjaxsettingsgui' => 
  array (
    'cid' => 'j3',
    'class_name' => 'ilMathJaxSettingsGUI',
    'class_path' => './Services/MathJax/classes/class.ilMathJaxSettingsGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjexternaltoolssettingsgui',
    ),
  ),
  'ilmediacasthandlergui' => 
  array (
    'cid' => 'j5',
    'class_name' => 'ilMediaCastHandlerGUI',
    'class_path' => './components/ILIAS/MediaCast/classes/class.ilMediaCastHandlerGUI.php',
    'children' => 
    array (
      0 => 'ilobjmediacastgui',
    ),
    'parents' => 
    array (
    ),
  ),
  'ilmediacreationgui' => 
  array (
    'cid' => 'j9',
    'class_name' => 'ilMediaCreationGUI',
    'class_path' => './Services/MediaObjects/Creation/class.ilMediaCreationGUI.php',
    'children' => 
    array (
      0 => 'ilpropertyformgui',
      1 => 'ilrepostandarduploadhandlergui',
    ),
    'parents' => 
    array (
      0 => 'ilobjmediacastgui',
      1 => 'ilobjmediapoolgui',
    ),
  ),
  'ilmediaobjectsplayerwrappergui' => 
  array (
    'cid' => 'jb',
    'class_name' => 'ilMediaObjectsPlayerWrapperGUI',
    'class_path' => './Services/MediaObjects/Player/class.ilMediaObjectsPlayerWrapperGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'mcstpodcastgui',
    ),
  ),
  'ilmediapoolimportgui' => 
  array (
    'cid' => 'jd',
    'class_name' => 'ilMediaPoolImportGUI',
    'class_path' => './components/ILIAS/MediaPool/classes/class.ilMediaPoolImportGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjmediapoolgui',
    ),
  ),
  'ilmediapoolpagegui' => 
  array (
    'cid' => 'je',
    'class_name' => 'ilMediaPoolPageGUI',
    'class_path' => './components/ILIAS/MediaPool/classes/class.ilMediaPoolPageGUI.php',
    'children' => 
    array (
      0 => 'ilpageeditorgui',
      1 => 'ileditclipboardgui',
      2 => 'ilpublicuserprofilegui',
      3 => 'ilobjectmetadatagui',
    ),
    'parents' => 
    array (
      0 => 'ilobjmediapoolgui',
    ),
  ),
  'ilmediapoolpresentationgui' => 
  array (
    'cid' => 'jg',
    'class_name' => 'ilMediaPoolPresentationGUI',
    'class_path' => './components/ILIAS/MediaPool/classes/class.ilMediaPoolPresentationGUI.php',
    'children' => 
    array (
      0 => 'ilobjmediapoolgui',
    ),
    'parents' => 
    array (
    ),
  ),
  'ilmemberagreementgui' => 
  array (
    'cid' => 'ji',
    'class_name' => 'ilMemberAgreementGUI',
    'class_path' => './Services/Membership/classes/class.ilMemberAgreementGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjcoursegui',
      1 => 'ilobjgroupgui',
    ),
  ),
  'ilmemberexportgui' => 
  array (
    'cid' => 'jk',
    'class_name' => 'ilMemberExportGUI',
    'class_path' => './Services/Membership/classes/Export/class.ilMemberExportGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilcoursemembershipgui',
      1 => 'ilgroupmembershipgui',
      2 => 'illearningsequencemembershipgui',
      3 => 'ilobjcoursegui',
      4 => 'ilobjgroupgui',
      5 => 'ilsessionmembershipgui',
    ),
  ),
  'ilmemberexportsettingsgui' => 
  array (
    'cid' => 'jl',
    'class_name' => 'ilMemberExportSettingsGUI',
    'class_path' => './Services/Membership/classes/Export/class.ilMemberExportSettingsGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
      1 => 'ilobjcourseadministrationgui',
      2 => 'ilobjcoursegui',
      3 => 'ilobjgroupadministrationgui',
    ),
  ),
  'ilmembershipblockgui' => 
  array (
    'cid' => 'jo',
    'class_name' => 'ilMembershipBlockGUI',
    'class_path' => './Services/Dashboard/Block/classes/class.ilMembershipBlockGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ildashboardgui',
      1 => 'ilmembershipoverviewgui',
    ),
  ),
  'ilmembershipmailgui' => 
  array (
    'cid' => 'jq',
    'class_name' => 'ilMembershipMailGUI',
    'class_path' => './Services/Membership/classes/class.ilMembershipMailGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjsessiongui',
    ),
  ),
  'ilmembershipoverviewgui' => 
  array (
    'cid' => 'jr',
    'class_name' => 'ilMembershipOverviewGUI',
    'class_path' => './Services/Membership/classes/class.ilMembershipOverviewGUI.php',
    'children' => 
    array (
      0 => 'ilmembershipblockgui',
    ),
    'parents' => 
    array (
      0 => 'ilstartupgui',
    ),
  ),
  'ilmessagegui' => 
  array (
    'cid' => 'jt',
    'class_name' => 'ilMessageGUI',
    'class_path' => './Services/Notes/Message/class.ilMessageGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilexpeerreviewgui',
    ),
  ),
  'ilmobmultisrtuploadgui' => 
  array (
    'cid' => 'jw',
    'class_name' => 'ilMobMultiSrtUploadGUI',
    'class_path' => './Services/MediaObjects/SubTitles/class.ilMobMultiSrtUploadGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjcontentobjectgui',
      1 => 'ilobjlearningmodulegui',
      2 => 'ilobjmediapoolgui',
    ),
  ),
  'ilmultilingualismgui' => 
  array (
    'cid' => 'k2',
    'class_name' => 'ilMultilingualismGUI',
    'class_path' => './Services/Multilingualism/classes/class.ilMultilingualismGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ildidactictemplatesettingsgui',
    ),
  ),
  'ilmystaffgui' => 
  array (
    'cid' => 'k8',
    'class_name' => 'ilMyStaffGUI',
    'class_path' => './Services/MyStaff/classes/class.ilMyStaffGUI.php',
    'children' => 
    array (
      0 => 'ilemployeetalkmystafflistgui',
      1 => 'ilmstlistcertificatesgui',
      2 => 'ilmstlistcompetencesgui',
      3 => 'ilmstlistcoursesgui',
      4 => 'ilmstlistusersgui',
      5 => 'ilmstshowusergui',
    ),
    'parents' => 
    array (
      0 => 'ildashboardgui',
    ),
  ),
  'ilmytestresultsgui' => 
  array (
    'cid' => 'k9',
    'class_name' => 'ilMyTestResultsGUI',
    'class_path' => './components/ILIAS/Test/classes/class.ilMyTestResultsGUI.php',
    'children' => 
    array (
      0 => 'iltestevaluationgui',
      1 => 'ilassquestionpagegui',
      2 => 'ilassspecfeedbackpagegui',
      3 => 'ilassgenfeedbackpagegui',
    ),
    'parents' => 
    array (
      0 => 'iltestresultsgui',
    ),
  ),
  'ilmytestsolutionsgui' => 
  array (
    'cid' => 'ka',
    'class_name' => 'ilMyTestSolutionsGUI',
    'class_path' => './components/ILIAS/Test/classes/class.ilMyTestSolutionsGUI.php',
    'children' => 
    array (
      0 => 'iltestevaluationgui',
      1 => 'ilassquestionpagegui',
    ),
    'parents' => 
    array (
      0 => 'iltestresultsgui',
    ),
  ),
  'ilnewsforcontextblockgui' => 
  array (
    'cid' => 'ke',
    'class_name' => 'ilNewsForContextBlockGUI',
    'class_path' => './Services/News/classes/class.ilNewsForContextBlockGUI.php',
    'children' => 
    array (
      0 => 'ilnewsitemgui',
    ),
    'parents' => 
    array (
      0 => 'ilcolumngui',
    ),
  ),
  'ilnewsitemgui' => 
  array (
    'cid' => 'kg',
    'class_name' => 'ilNewsItemGUI',
    'class_path' => './Services/News/classes/class.ilNewsItemGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'illmpagegui',
      1 => 'ilnewsforcontextblockgui',
      2 => 'ilpageobjectgui',
    ),
  ),
  'ilnewstimelinegui' => 
  array (
    'cid' => 'kh',
    'class_name' => 'ilNewsTimelineGUI',
    'class_path' => './Services/News/classes/class.ilNewsTimelineGUI.php',
    'children' => 
    array (
      0 => 'illikegui',
      1 => 'ilcommentgui',
    ),
    'parents' => 
    array (
      0 => 'ilobjcoursegui',
      1 => 'ilobjgroupgui',
      2 => 'ilpdnewsgui',
    ),
  ),
  'ilnotegui' => 
  array (
    'cid' => 'kk',
    'class_name' => 'ilNoteGUI',
    'class_path' => './Services/Notes/Note/class.ilNoteGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilassgenfeedbackpagegui',
      1 => 'ilasshintpagegui',
      2 => 'ilassquestionpagegui',
      3 => 'ilassquestionpreviewgui',
      4 => 'ilassspecfeedbackpagegui',
      5 => 'ilblogpostinggui',
      6 => 'ilcommonactiondispatchergui',
      7 => 'ilcontainerpagegui',
      8 => 'ilcontainerstartobjectspagegui',
      9 => 'ilcontentpagepagegui',
      10 => 'ilforumpagegui',
      11 => 'ilglossarydefpagegui',
      12 => 'ilglossarypresentationgui',
      13 => 'illopagegui',
      14 => 'illoginpagegui',
      15 => 'ilobjbibliographicgui',
      16 => 'ilobjbloggui',
      17 => 'ilobjcloudgui',
      18 => 'ilobjdatacollectiongui',
      19 => 'ilobjpollgui',
      20 => 'ilobjportfoliogui',
      21 => 'ilobjportfoliotemplategui',
      22 => 'ilobjscorm2004learningmodulegui',
      23 => 'ilpdnotesgui',
      24 => 'ilpageobjectgui',
      25 => 'iltestexpresspageobjectgui',
      26 => 'iltestpagegui',
      27 => 'ilwikipagegui',
    ),
  ),
  'ilnotificationgui' => 
  array (
    'cid' => 'kl',
    'class_name' => 'ilNotificationGUI',
    'class_path' => './Services/Notifications/classes/class.ilNotificationGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
    ),
  ),
  'ilobjaccessibilitysettingsgui' => 
  array (
    'cid' => 'ko',
    'class_name' => 'ilObjAccessibilitySettingsGUI',
    'class_path' => './Services/Accessibility/classes/class.ilObjAccessibilitySettingsGUI.php',
    'children' => 
    array (
      0 => 'ilpermissiongui',
      1 => 'ilaccessibilitydocumentgui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
    ),
  ),
  'ilobjadministrativenotificationgui' => 
  array (
    'cid' => 'kp',
    'class_name' => 'ilObjAdministrativeNotificationGUI',
    'class_path' => './Services/AdministrativeNotification/classes/class.ilObjAdministrativeNotificationGUI.php',
    'children' => 
    array (
      0 => 'ilpermissiongui',
      1 => 'iladnnotificationgui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
    ),
  ),
  'ilobjadvancededitinggui' => 
  array (
    'cid' => 'kq',
    'class_name' => 'ilObjAdvancedEditingGUI',
    'class_path' => './Services/AdvancedEditing/classes/class.ilObjAdvancedEditingGUI.php',
    'children' => 
    array (
      0 => 'ilpermissiongui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
    ),
  ),
  'ilobjassessmentfoldergui' => 
  array (
    'cid' => 'kr',
    'class_name' => 'ilObjAssessmentFolderGUI',
    'class_path' => './components/ILIAS/Test/classes/class.ilObjAssessmentFolderGUI.php',
    'children' => 
    array (
      0 => 'ilpermissiongui',
      1 => 'ilglobalunitconfigurationgui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
    ),
  ),
  'ilobjauthsettingsgui' => 
  array (
    'cid' => 'ks',
    'class_name' => 'ilObjAuthSettingsGUI',
    'class_path' => './Services/Authentication/classes/class.ilObjAuthSettingsGUI.php',
    'children' => 
    array (
      0 => 'ilpermissiongui',
      1 => 'ilregistrationsettingsgui',
      2 => 'illdapsettingsgui',
      3 => 'ilauthshibbolethsettingsgui',
      4 => 'ilcassettingsgui',
      5 => 'ilsamlsettingsgui',
      6 => 'ilopenidconnectsettingsgui',
      7 => 'ilauthloginpageeditorgui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
    ),
  ),
  'ilobjawarenessadministrationgui' => 
  array (
    'cid' => 'ku',
    'class_name' => 'ilObjAwarenessAdministrationGUI',
    'class_path' => './Services/Awareness/Administration/class.ilObjAwarenessAdministrationGUI.php',
    'children' => 
    array (
      0 => 'ilpermissiongui',
      1 => 'iluseractionadmingui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
    ),
  ),
  'ilobjbadgeadministrationgui' => 
  array (
    'cid' => 'kv',
    'class_name' => 'ilObjBadgeAdministrationGUI',
    'class_path' => './Services/Badge/classes/class.ilObjBadgeAdministrationGUI.php',
    'children' => 
    array (
      0 => 'ilpermissiongui',
      1 => 'ilbadgemanagementgui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
    ),
  ),
  'ilobjbibliographicadmingui' => 
  array (
    'cid' => 'kw',
    'class_name' => 'ilObjBibliographicAdminGUI',
    'class_path' => './components/ILIAS/Bibliographic/classes/class.ilObjBibliographicAdminGUI.php',
    'children' => 
    array (
      0 => 'ilpermissiongui',
      1 => 'ilbibladminfieldgui',
      2 => 'ilbibllibrarygui',
      3 => 'ilbibladminrisfieldgui',
      4 => 'ilbibladminbibtexfieldgui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
    ),
  ),
  'ilobjbibliographicgui' => 
  array (
    'cid' => 'kx',
    'class_name' => 'ilObjBibliographicGUI',
    'class_path' => './components/ILIAS/Bibliographic/classes/class.ilObjBibliographicGUI.php',
    'children' => 
    array (
      0 => 'ilinfoscreengui',
      1 => 'ilnotegui',
      2 => 'ilcommonactiondispatchergui',
      3 => 'ilpermissiongui',
      4 => 'ilobjectcopygui',
      5 => 'ilexportgui',
      6 => 'ilobjusergui',
      7 => 'ilbiblentrytablegui',
      8 => 'ilbiblfieldfiltergui',
      9 => 'ilobjbibliographicuploadhandlergui',
    ),
    'parents' => 
    array (
      0 => 'ilrepositorygui',
      1 => 'iladministrationgui',
    ),
  ),
  'ilobjbibliographicuploadhandlergui' => 
  array (
    'cid' => 'kz',
    'class_name' => 'ilObjBibliographicUploadHandlerGUI',
    'class_path' => './components/ILIAS/Bibliographic/classes/class.ilObjBibliographicUploadHandlerGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjbibliographicgui',
      1 => 'ilrepositorygui',
      2 => 'ildashboardgui',
    ),
  ),
  'ilobjblogadministrationgui' => 
  array (
    'cid' => 'l0',
    'class_name' => 'ilObjBlogAdministrationGUI',
    'class_path' => './components/ILIAS/Blog/Administration/class.ilObjBlogAdministrationGUI.php',
    'children' => 
    array (
      0 => 'ilpermissiongui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
    ),
  ),
  'ilobjbloggui' => 
  array (
    'cid' => 'l1',
    'class_name' => 'ilObjBlogGUI',
    'class_path' => './components/ILIAS/Blog/classes/class.ilObjBlogGUI.php',
    'children' => 
    array (
      0 => 'ilblogpostinggui',
      1 => 'ilworkspaceaccessgui',
      2 => 'ilportfoliopagegui',
      3 => 'ilinfoscreengui',
      4 => 'ilnotegui',
      5 => 'ilcommonactiondispatchergui',
      6 => 'ilpermissiongui',
      7 => 'ilobjectcopygui',
      8 => 'ilrepositorysearchgui',
      9 => 'ilexportgui',
      10 => 'ilobjectcontentstylesettingsgui',
      11 => 'ilblogexercisegui',
      12 => 'ilobjnotificationsettingsgui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
      1 => 'ilpersonalworkspacegui',
      2 => 'ilportfoliopagegui',
      3 => 'ilrepositorygui',
      4 => 'ilsharedresourcegui',
    ),
  ),
  'ilobjbookingpoolgui' => 
  array (
    'cid' => 'l3',
    'class_name' => 'ilObjBookingPoolGUI',
    'class_path' => './components/ILIAS/BookingManager/classes/class.ilObjBookingPoolGUI.php',
    'children' => 
    array (
      0 => 'ilpermissiongui',
      1 => 'ilbookingobjectgui',
      2 => 'ildidactictemplategui',
      3 => 'ilbookingschedulegui',
      4 => 'ilinfoscreengui',
      5 => 'ilpublicuserprofilegui',
      6 => 'ilcommonactiondispatchergui',
      7 => 'ilobjectcopygui',
      8 => 'ilobjectmetadatagui',
      9 => 'ilbookingparticipantgui',
      10 => 'ilbookingreservationsgui',
      11 => 'ilbookingpreferencesgui',
    ),
    'parents' => 
    array (
      0 => 'ilrepositorygui',
      1 => 'iladministrationgui',
    ),
  ),
  'ilobjcalendarsettingsgui' => 
  array (
    'cid' => 'l5',
    'class_name' => 'ilObjCalendarSettingsGUI',
    'class_path' => './Services/Calendar/classes/class.ilObjCalendarSettingsGUI.php',
    'children' => 
    array (
      0 => 'ilpermissiongui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
    ),
  ),
  'ilobjcategorygui' => 
  array (
    'cid' => 'l6',
    'class_name' => 'ilObjCategoryGUI',
    'class_path' => './components/ILIAS/Category/classes/class.ilObjCategoryGUI.php',
    'children' => 
    array (
      0 => 'ilpermissiongui',
      1 => 'ilcontainerpagegui',
      2 => 'ilobjusergui',
      3 => 'ilobjuserfoldergui',
      4 => 'ilinfoscreengui',
      5 => 'ilobjstylesheetgui',
      6 => 'ilcommonactiondispatchergui',
      7 => 'ilobjecttranslationgui',
      8 => 'ilobjectcontentstylesettingsgui',
      9 => 'ilcolumngui',
      10 => 'ilobjectcopygui',
      11 => 'ilusertablegui',
      12 => 'ildidactictemplategui',
      13 => 'ilexportgui',
      14 => 'iltaxonomysettingsgui',
      15 => 'ilobjectmetadatagui',
      16 => 'ilcontainernewssettingsgui',
      17 => 'ilcontainerfilteradmingui',
      18 => 'ilrepositorytrashgui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
      1 => 'iladvancedsearchgui',
      2 => 'illuceneadvancedsearchgui',
      3 => 'illucenesearchgui',
      4 => 'ilrepositorygui',
      5 => 'ilsearchgui',
    ),
  ),
  'ilobjcategoryreferencegui' => 
  array (
    'cid' => 'l8',
    'class_name' => 'ilObjCategoryReferenceGUI',
    'class_path' => './components/ILIAS/CategoryReference/classes/class.ilObjCategoryReferenceGUI.php',
    'children' => 
    array (
      0 => 'ilpermissiongui',
      1 => 'ilinfoscreengui',
      2 => 'ilpropertyformgui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
      1 => 'ilrepositorygui',
    ),
  ),
  'ilobjcertificatesettingsgui' => 
  array (
    'cid' => 'la',
    'class_name' => 'ilObjCertificateSettingsGUI',
    'class_path' => './Services/Certificate/classes/class.ilObjCertificateSettingsGUI.php',
    'children' => 
    array (
      0 => 'ilpermissiongui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
    ),
  ),
  'ilobjchatroomadmingui' => 
  array (
    'cid' => 'lb',
    'class_name' => 'ilObjChatroomAdminGUI',
    'class_path' => './components/ILIAS/Chatroom/classes/class.ilObjChatroomAdminGUI.php',
    'children' => 
    array (
      0 => 'ilmdeditorgui',
      1 => 'ilinfoscreengui',
      2 => 'ilpermissiongui',
      3 => 'ilobjectcopygui',
      4 => 'ilexportgui',
      5 => 'ilobjchatroomgui',
    ),
    'parents' => 
    array (
      0 => 'ilrepositorygui',
      1 => 'iladministrationgui',
    ),
  ),
  'ilobjchatroomgui' => 
  array (
    'cid' => 'lc',
    'class_name' => 'ilObjChatroomGUI',
    'class_path' => './components/ILIAS/Chatroom/classes/class.ilObjChatroomGUI.php',
    'children' => 
    array (
      0 => 'ilmdeditorgui',
      1 => 'ilinfoscreengui',
      2 => 'ilpermissiongui',
      3 => 'ilobjectcopygui',
      4 => 'ilexportgui',
      5 => 'ilcommonactiondispatchergui',
      6 => 'ilpropertyformgui',
      7 => 'ilexportgui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
      1 => 'ildashboardgui',
      2 => 'ilobjchatroomadmingui',
      3 => 'ilrepositorygui',
    ),
  ),
  'ilobjcloudgui' => 
  array (
    'cid' => 'lf',
    'class_name' => 'ilObjCloudGUI',
    'class_path' => './components/ILIAS/Cloud/classes/class.ilObjCloudGUI.php',
    'children' => 
    array (
      0 => 'ilpermissiongui',
      1 => 'ilnotegui',
      2 => 'ilinfoscreengui',
      3 => 'ilobjectcopygui',
      4 => 'ilcommonactiondispatchergui',
      5 => 'ilcloudplugincreatefoldergui',
      6 => 'ilcloudplugindeletegui',
      7 => 'ilcloudpluginitemcreationlistgui',
      8 => 'ilcloudplugininitgui',
      9 => 'ilcloudplugininfoscreengui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
      1 => 'ilrepositorygui',
    ),
  ),
  'ilobjcmixapiadministrationgui' => 
  array (
    'cid' => 'lh',
    'class_name' => 'ilObjCmiXapiAdministrationGUI',
    'class_path' => './components/ILIAS/CmiXapi/classes/class.ilObjCmiXapiAdministrationGUI.php',
    'children' => 
    array (
      0 => 'ilpermissiongui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
    ),
  ),
  'ilobjcmixapigui' => 
  array (
    'cid' => 'li',
    'class_name' => 'ilObjCmiXapiGUI',
    'class_path' => './components/ILIAS/CmiXapi/classes/class.ilObjCmiXapiGUI.php',
    'children' => 
    array (
      0 => 'ilobjectcopygui',
      1 => 'ilcommonactiondispatchergui',
      2 => 'ilobjectmetadatagui',
      3 => 'ilpermissiongui',
      4 => 'ilinfoscreengui',
      5 => 'illearningprogressgui',
      6 => 'ilcmixapiregistrationgui',
      7 => 'ilcmixapilaunchgui',
      8 => 'ilcmixapisettingsgui',
      9 => 'ilcmixapistatementsgui',
      10 => 'ilcmixapiscoringgui',
      11 => 'ilcmixapiexportgui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
      1 => 'ilrepositorygui',
    ),
  ),
  'ilobjcmixapiverificationgui' => 
  array (
    'cid' => 'lk',
    'class_name' => 'ilObjCmiXapiVerificationGUI',
    'class_path' => './components/ILIAS/CmiXapi/classes/Verification/class.ilObjCmiXapiVerificationGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilpersonalworkspacegui',
    ),
  ),
  'ilobjcommentssettingsgui' => 
  array (
    'cid' => 'lm',
    'class_name' => 'ilObjCommentsSettingsGUI',
    'class_path' => './Services/Notes/Administration/classes/class.ilObjCommentsSettingsGUI.php',
    'children' => 
    array (
      0 => 'ilpermissiongui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
    ),
  ),
  'ilobjcomponentsettingsgui' => 
  array (
    'cid' => 'ln',
    'class_name' => 'ilObjComponentSettingsGUI',
    'class_path' => './Services/Component/classes/Settings/class.ilObjComponentSettingsGUI.php',
    'children' => 
    array (
      0 => 'ilpermissiongui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
    ),
  ),
  'ilobjcontactadministrationgui' => 
  array (
    'cid' => 'lo',
    'class_name' => 'ilObjContactAdministrationGUI',
    'class_path' => './Services/Contact/classes/class.ilObjContactAdministrationGUI.php',
    'children' => 
    array (
      0 => 'ilpermissiongui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
    ),
  ),
  'ilobjcontentobjectgui' => 
  array (
    'cid' => 'lp',
    'class_name' => 'ilObjContentObjectGUI',
    'class_path' => './components/ILIAS/LearningModule/classes/class.ilObjContentObjectGUI.php',
    'children' => 
    array (
      0 => 'illmpageobjectgui',
      1 => 'ilstructureobjectgui',
      2 => 'ilobjectcontentstylesettingsgui',
      3 => 'ilobjectmetadatagui',
      4 => 'illearningprogressgui',
      5 => 'ilpermissiongui',
      6 => 'ilinfoscreengui',
      7 => 'ilobjectcopygui',
      8 => 'ilexportgui',
      9 => 'ilcommonactiondispatchergui',
      10 => 'ilpagemultilanggui',
      11 => 'ilobjecttranslationgui',
      12 => 'ilmobmultisrtuploadgui',
      13 => 'illmimportgui',
      14 => 'illmeditshorttitlesgui',
      15 => 'illtiproviderobjectsettinggui',
    ),
    'parents' => 
    array (
    ),
  ),
  'ilobjcontentpageadministrationgui' => 
  array (
    'cid' => 'lq',
    'class_name' => 'ilObjContentPageAdministrationGUI',
    'class_path' => './components/ILIAS/ContentPage/classes/class.ilObjContentPageAdministrationGUI.php',
    'children' => 
    array (
      0 => 'ilpermissiongui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
    ),
  ),
  'ilobjcontentpagegui' => 
  array (
    'cid' => 'lr',
    'class_name' => 'ilObjContentPageGUI',
    'class_path' => './components/ILIAS/ContentPage/classes/class.ilObjContentPageGUI.php',
    'children' => 
    array (
      0 => 'ilpermissiongui',
      1 => 'ilinfoscreengui',
      2 => 'ilobjectcopygui',
      3 => 'ilexportgui',
      4 => 'illearningprogressgui',
      5 => 'ilcommonactiondispatchergui',
      6 => 'ilcontentpagepagegui',
      7 => 'ilobjectcontentstylesettingsgui',
      8 => 'ilobjecttranslationgui',
      9 => 'ilpagemultilanggui',
      10 => 'ilmdeditorgui',
    ),
    'parents' => 
    array (
      0 => 'ilrepositorygui',
      1 => 'iladministrationgui',
      2 => 'ilobjlearningsequencegui',
    ),
  ),
  'ilobjcourseadministrationgui' => 
  array (
    'cid' => 'lt',
    'class_name' => 'ilObjCourseAdministrationGUI',
    'class_path' => './components/ILIAS/Course/classes/class.ilObjCourseAdministrationGUI.php',
    'children' => 
    array (
      0 => 'ilpermissiongui',
      1 => 'ilmemberexportsettingsgui',
      2 => 'iluseractionadmingui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
    ),
  ),
  'ilobjcoursegui' => 
  array (
    'cid' => 'lu',
    'class_name' => 'ilObjCourseGUI',
    'class_path' => './components/ILIAS/Course/classes/class.ilObjCourseGUI.php',
    'children' => 
    array (
      0 => 'ilcourseregistrationgui',
      1 => 'ilcourseobjectivesgui',
      2 => 'ilobjcoursegroupinggui',
      3 => 'ilinfoscreengui',
      4 => 'illearningprogressgui',
      5 => 'ilpermissiongui',
      6 => 'ilrepositorysearchgui',
      7 => 'ilconditionhandlergui',
      8 => 'ilcoursecontentgui',
      9 => 'ilpublicuserprofilegui',
      10 => 'ilmemberexportgui',
      11 => 'ilobjectcustomuserfieldsgui',
      12 => 'ilmemberagreementgui',
      13 => 'ilsessionoverviewgui',
      14 => 'ilcolumngui',
      15 => 'ilcontainerpagegui',
      16 => 'ilobjectcopygui',
      17 => 'ilobjectcontentstylesettingsgui',
      18 => 'ilcourseparticipantsgroupsgui',
      19 => 'ilexportgui',
      20 => 'ilcommonactiondispatchergui',
      21 => 'ildidactictemplategui',
      22 => 'ilcertificategui',
      23 => 'ilobjectservicesettingsgui',
      24 => 'ilcontainerstartobjectsgui',
      25 => 'ilcontainerstartobjectspagegui',
      26 => 'ilmailmembersearchgui',
      27 => 'ilbadgemanagementgui',
      28 => 'illopagegui',
      29 => 'ilobjectmetadatagui',
      30 => 'ilnewstimelinegui',
      31 => 'ilcontainernewssettingsgui',
      32 => 'ilcoursemembershipgui',
      33 => 'ilpropertyformgui',
      34 => 'ilcontainerskillgui',
      35 => 'ilcalendarpresentationgui',
      36 => 'ilmemberexportsettingsgui',
      37 => 'illtiproviderobjectsettinggui',
      38 => 'ilobjecttranslationgui',
      39 => 'ilbookinggatewaygui',
      40 => 'ilrepositorytrashgui',
      41 => 'illoeditorgui',
      42 => 'illomembertestresultgui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
      1 => 'iladvancedsearchgui',
      2 => 'illuceneadvancedsearchgui',
      3 => 'illucenesearchgui',
      4 => 'ilobjtestgui',
      5 => 'ilrepositorygui',
      6 => 'ilsearchgui',
    ),
  ),
  'ilobjcoursegroupinggui' => 
  array (
    'cid' => 'lv',
    'class_name' => 'ilObjCourseGroupingGUI',
    'class_path' => './components/ILIAS/Course/classes/class.ilObjCourseGroupingGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjcoursegui',
      1 => 'ilobjgroupgui',
    ),
  ),
  'ilobjcoursereferencegui' => 
  array (
    'cid' => 'lx',
    'class_name' => 'ilObjCourseReferenceGUI',
    'class_path' => './components/ILIAS/CourseReference/classes/class.ilObjCourseReferenceGUI.php',
    'children' => 
    array (
      0 => 'ilpermissiongui',
      1 => 'ilinfoscreengui',
      2 => 'ilpropertyformgui',
      3 => 'ilcommonactiondispatchergui',
      4 => 'illearningprogressgui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
      1 => 'ilrepositorygui',
    ),
  ),
  'ilobjcourseverificationgui' => 
  array (
    'cid' => 'lz',
    'class_name' => 'ilObjCourseVerificationGUI',
    'class_path' => './components/ILIAS/Course/classes/Verification/class.ilObjCourseVerificationGUI.php',
    'children' => 
    array (
      0 => 'ilworkspaceaccessgui',
    ),
    'parents' => 
    array (
      0 => 'ilpersonalworkspacegui',
    ),
  ),
  'ilobjdashboardsettingsgui' => 
  array (
    'cid' => 'm1',
    'class_name' => 'ilObjDashboardSettingsGUI',
    'class_path' => './Services/Dashboard/Administration/classes/class.ilObjDashboardSettingsGUI.php',
    'children' => 
    array (
      0 => 'ilpermissiongui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
    ),
  ),
  'ilobjdatacollectiongui' => 
  array (
    'cid' => 'm2',
    'class_name' => 'ilObjDataCollectionGUI',
    'class_path' => './components/ILIAS/DataCollection/classes/class.ilObjDataCollectionGUI.php',
    'children' => 
    array (
      0 => 'ilinfoscreengui',
      1 => 'ilnotegui',
      2 => 'ilcommonactiondispatchergui',
      3 => 'ilpermissiongui',
      4 => 'ilobjectcopygui',
      5 => 'ildclexportgui',
      6 => 'ildclrecordlistgui',
      7 => 'ildclrecordeditgui',
      8 => 'ildcldetailedviewgui',
      9 => 'ildcltablelistgui',
      10 => 'ilobjfilegui',
      11 => 'ilobjusergui',
      12 => 'ilratinggui',
      13 => 'ilpropertyformgui',
      14 => 'ildclpropertyformgui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
      1 => 'ilrepositorygui',
    ),
  ),
  'ilobjdataprotectiongui' => 
  array (
    'cid' => 'm4',
    'class_name' => 'ilObjDataProtectionGUI',
    'class_path' => './Services/DataProtection/classes/class.ilObjDataProtectionGUI.php',
    'children' => 
    array (
      0 => 'illegaldocumentsadministrationgui',
      1 => 'ilpermissiongui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
    ),
  ),
  'ilobjecssettingsgui' => 
  array (
    'cid' => 'm5',
    'class_name' => 'ilObjECSSettingsGUI',
    'class_path' => './Services/WebServices/ECS/classes/class.ilObjECSSettingsGUI.php',
    'children' => 
    array (
      0 => 'ilpermissiongui',
      1 => 'ilecssettingsgui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
    ),
  ),
  'ilobjemployeetalkgui' => 
  array (
    'cid' => 'm6',
    'class_name' => 'ilObjEmployeeTalkGUI',
    'class_path' => './components/ILIAS/EmployeeTalk/classes/Talk/class.ilObjEmployeeTalkGUI.php',
    'children' => 
    array (
      0 => 'ilcommonactiondispatchergui',
      1 => 'ilrepositorysearchgui',
      2 => 'ilcolumngui',
      3 => 'ilobjectcopygui',
      4 => 'ilusertablegui',
      5 => 'ilpermissiongui',
      6 => 'ilinfoscreengui',
      7 => 'ilpropertyformgui',
      8 => 'ilemployeetalkappointmentgui',
    ),
    'parents' => 
    array (
      0 => 'ilemployeetalkmystafflistgui',
      1 => 'ilemployeetalkmystaffusergui',
    ),
  ),
  'ilobjemployeetalkseriesgui' => 
  array (
    'cid' => 'm8',
    'class_name' => 'ilObjEmployeeTalkSeriesGUI',
    'class_path' => './components/ILIAS/EmployeeTalk/classes/TalkSeries/class.ilObjEmployeeTalkSeriesGUI.php',
    'children' => 
    array (
      0 => 'ilcommonactiondispatchergui',
      1 => 'ilrepositorysearchgui',
      2 => 'ilcolumngui',
      3 => 'ilobjectcopygui',
      4 => 'ilusertablegui',
      5 => 'ilpermissiongui',
      6 => 'ilinfoscreengui',
      7 => 'ilobjfilegui',
      8 => 'ilobjfileuploadhandlergui',
    ),
    'parents' => 
    array (
      0 => 'ilemployeetalkmystafflistgui',
      1 => 'ilemployeetalkmystaffusergui',
      2 => 'iladministrationgui',
      3 => 'ilobjtalktemplateadministrationgui',
    ),
  ),
  'ilobjemployeetalkserieslistgui' => 
  array (
    'cid' => 'm9',
    'class_name' => 'ilObjEmployeeTalkSeriesListGUI',
    'class_path' => './components/ILIAS/EmployeeTalk/classes/TalkSeries/class.ilObjEmployeeTalkSeriesListGUI.php',
    'children' => 
    array (
      0 => 'ilformpropertydispatchgui',
    ),
    'parents' => 
    array (
    ),
  ),
  'ilobjexerciseadministrationgui' => 
  array (
    'cid' => 'ma',
    'class_name' => 'ilObjExerciseAdministrationGUI',
    'class_path' => './components/ILIAS/Exercise/classes/class.ilObjExerciseAdministrationGUI.php',
    'children' => 
    array (
      0 => 'ilpermissiongui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
    ),
  ),
  'ilobjexercisegui' => 
  array (
    'cid' => 'mb',
    'class_name' => 'ilObjExerciseGUI',
    'class_path' => './components/ILIAS/Exercise/classes/class.ilObjExerciseGUI.php',
    'children' => 
    array (
      0 => 'ilpermissiongui',
      1 => 'illearningprogressgui',
      2 => 'ilinfoscreengui',
      3 => 'ilobjectcopygui',
      4 => 'ilexportgui',
      5 => 'ilcommonactiondispatchergui',
      6 => 'ilcertificategui',
      7 => 'ilexassignmenteditorgui',
      8 => 'ilassignmentpresentationgui',
      9 => 'ilexercisemanagementgui',
      10 => 'ilexccriteriacataloguegui',
      11 => 'ilobjectmetadatagui',
      12 => 'ilportfolioexercisegui',
      13 => 'ilexcrandomassignmentgui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
      1 => 'ilexercisehandlergui',
      2 => 'ilobjlearningsequencegui',
      3 => 'ilportfoliorepositorygui',
      4 => 'ilrepositorygui',
    ),
  ),
  'ilobjexerciseverificationgui' => 
  array (
    'cid' => 'me',
    'class_name' => 'ilObjExerciseVerificationGUI',
    'class_path' => './components/ILIAS/Exercise/classes/class.ilObjExerciseVerificationGUI.php',
    'children' => 
    array (
      0 => 'ilworkspaceaccessgui',
    ),
    'parents' => 
    array (
      0 => 'ilpersonalworkspacegui',
      1 => 'ilsharedresourcegui',
    ),
  ),
  'ilobjexternaltoolssettingsgui' => 
  array (
    'cid' => 'mg',
    'class_name' => 'ilObjExternalToolsSettingsGUI',
    'class_path' => './Services/Administration/classes/class.ilObjExternalToolsSettingsGUI.php',
    'children' => 
    array (
      0 => 'ilpermissiongui',
      1 => 'ilmathjaxsettingsgui',
      2 => 'ilwopiadministrationgui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
    ),
  ),
  'ilobjfileaccesssettingsgui' => 
  array (
    'cid' => 'mh',
    'class_name' => 'ilObjFileAccessSettingsGUI',
    'class_path' => './components/ILIAS/File/classes/class.ilObjFileAccessSettingsGUI.php',
    'children' => 
    array (
      0 => 'ilpermissiongui',
      1 => 'ilias\\file\\icon\\ilobjfileiconsoverviewgui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
    ),
  ),
  'ilobjfilebasedlmgui' => 
  array (
    'cid' => 'mi',
    'class_name' => 'ilObjFileBasedLMGUI',
    'class_path' => './components/ILIAS/HTMLLearningModule/classes/class.ilObjFileBasedLMGUI.php',
    'children' => 
    array (
      0 => 'ilfilesystemgui',
      1 => 'ilobjectmetadatagui',
      2 => 'ilpermissiongui',
      3 => 'illearningprogressgui',
      4 => 'ilinfoscreengui',
      5 => 'ilcommonactiondispatchergui',
      6 => 'ilexportgui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
      1 => 'ilhtlmeditorgui',
      2 => 'ilhtlmpresentationgui',
      3 => 'ilobjlearningsequencegui',
      4 => 'ilrepositorygui',
    ),
  ),
  'ilobjfilegui' => 
  array (
    'cid' => 'mk',
    'class_name' => 'ilObjFileGUI',
    'class_path' => './components/ILIAS/File/classes/class.ilObjFileGUI.php',
    'children' => 
    array (
      0 => 'ilobjectmetadatagui',
      1 => 'ilinfoscreengui',
      2 => 'ilpermissiongui',
      3 => 'ilobjectcopygui',
      4 => 'ilexportgui',
      5 => 'ilworkspaceaccessgui',
      6 => 'ilportfoliopagegui',
      7 => 'ilcommonactiondispatchergui',
      8 => 'illearningprogressgui',
      9 => 'ilfileversionsgui',
      10 => 'ilwopiembeddedapplicationgui',
      11 => 'ilobjfileuploadhandlergui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
      1 => 'ilobjdatacollectiongui',
      2 => 'ilobjemployeetalkseriesgui',
      3 => 'ilobjlearningsequencegui',
      4 => 'ilobjstudyprogrammemembersgui',
      5 => 'ilpersonalworkspacegui',
      6 => 'ilrepositorygui',
      7 => 'ilsharedresourcegui',
    ),
  ),
  'ilobjfilepreviewrenderergui' => 
  array (
    'cid' => 'mm',
    'class_name' => 'ilObjFilePreviewRendererGUI',
    'class_path' => './components/ILIAS/File/classes/Preview/class.ilObjFilePreviewRendererGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
    ),
  ),
  'ilobjfileservicesgui' => 
  array (
    'cid' => 'mn',
    'class_name' => 'ilObjFileServicesGUI',
    'class_path' => './Services/FileServices/classes/class.ilObjFileServicesGUI.php',
    'children' => 
    array (
      0 => 'ilpermissiongui',
      1 => 'ilresourceoverviewgui',
      2 => 'iluploadlimitsoverviewgui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
    ),
  ),
  'ilobjfileuploadhandlergui' => 
  array (
    'cid' => 'mo',
    'class_name' => 'ilObjFileUploadHandlerGUI',
    'class_path' => './components/ILIAS/File/classes/class.ilObjFileUploadHandlerGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjfilegui',
      1 => 'ilrepositorygui',
      2 => 'ildashboardgui',
      3 => 'ilobjemployeetalkseriesgui',
      4 => 'ilobjlearningsequencegui',
    ),
  ),
  'ilobjfoldergui' => 
  array (
    'cid' => 'mp',
    'class_name' => 'ilObjFolderGUI',
    'class_path' => './components/ILIAS/Folder/classes/class.ilObjFolderGUI.php',
    'children' => 
    array (
      0 => 'ilpermissiongui',
      1 => 'ilcoursecontentgui',
      2 => 'illearningprogressgui',
      3 => 'ilinfoscreengui',
      4 => 'ilcontainerpagegui',
      5 => 'ilcolumngui',
      6 => 'ilobjectcopygui',
      7 => 'ilobjectcontentstylesettingsgui',
      8 => 'ilexportgui',
      9 => 'ilcommonactiondispatchergui',
      10 => 'ildidactictemplategui',
      11 => 'ilobjecttranslationgui',
      12 => 'ilrepositorytrashgui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
      1 => 'iladvancedsearchgui',
      2 => 'illuceneadvancedsearchgui',
      3 => 'illucenesearchgui',
      4 => 'ilobjmediapoolgui',
      5 => 'ilrepositorygui',
      6 => 'ilsearchgui',
    ),
  ),
  'ilobjforumadministrationgui' => 
  array (
    'cid' => 'mr',
    'class_name' => 'ilObjForumAdministrationGUI',
    'class_path' => './components/ILIAS/Forum/classes/class.ilObjForumAdministrationGUI.php',
    'children' => 
    array (
      0 => 'ilpermissiongui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
    ),
  ),
  'ilobjforumgui' => 
  array (
    'cid' => 'ms',
    'class_name' => 'ilObjForumGUI',
    'class_path' => './components/ILIAS/Forum/classes/class.ilObjForumGUI.php',
    'children' => 
    array (
      0 => 'ilpermissiongui',
      1 => 'ilforumexportgui',
      2 => 'ilinfoscreengui',
      3 => 'ilcolumngui',
      4 => 'ilpublicuserprofilegui',
      5 => 'ilforummoderatorsgui',
      6 => 'ilrepositoryobjectsearchgui',
      7 => 'ilobjectcopygui',
      8 => 'ilexportgui',
      9 => 'ilcommonactiondispatchergui',
      10 => 'ilratinggui',
      11 => 'ilforumsettingsgui',
      12 => 'ilcontainernewssettingsgui',
      13 => 'illearningprogressgui',
      14 => 'ilforumpagegui',
      15 => 'ilobjectcontentstylesettingsgui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
      1 => 'ilrepositorygui',
    ),
  ),
  'ilobjglossarygui' => 
  array (
    'cid' => 'mw',
    'class_name' => 'ilObjGlossaryGUI',
    'class_path' => './components/ILIAS/Glossary/classes/class.ilObjGlossaryGUI.php',
    'children' => 
    array (
      0 => 'ilglossarytermgui',
      1 => 'ilmdeditorgui',
      2 => 'ilpermissiongui',
      3 => 'ilinfoscreengui',
      4 => 'ilcommonactiondispatchergui',
      5 => 'ilobjectcontentstylesettingsgui',
      6 => 'iltaxonomysettingsgui',
      7 => 'ilexportgui',
      8 => 'ilobjectcopygui',
      9 => 'ilobjectmetadatagui',
      10 => 'ilglossaryforeigntermcollectorgui',
      11 => 'iltermdefinitionbulkcreationgui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
      1 => 'ilglossaryeditorgui',
      2 => 'ilrepositorygui',
    ),
  ),
  'ilobjgroupadministrationgui' => 
  array (
    'cid' => 'mz',
    'class_name' => 'ilObjGroupAdministrationGUI',
    'class_path' => './components/ILIAS/Group/classes/class.ilObjGroupAdministrationGUI.php',
    'children' => 
    array (
      0 => 'ilpermissiongui',
      1 => 'ilmemberexportsettingsgui',
      2 => 'iluseractionadmingui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
    ),
  ),
  'ilobjgroupgui' => 
  array (
    'cid' => 'n0',
    'class_name' => 'ilObjGroupGUI',
    'class_path' => './components/ILIAS/Group/classes/class.ilObjGroupGUI.php',
    'children' => 
    array (
      0 => 'ilgroupregistrationgui',
      1 => 'ilpermissiongui',
      2 => 'ilinfoscreengui',
      3 => 'illearningprogressgui',
      4 => 'ilpublicuserprofilegui',
      5 => 'ilobjcoursegroupinggui',
      6 => 'ilobjectcontentstylesettingsgui',
      7 => 'ilcoursecontentgui',
      8 => 'ilcolumngui',
      9 => 'ilcontainerpagegui',
      10 => 'ilobjectcopygui',
      11 => 'ilobjectcustomuserfieldsgui',
      12 => 'ilmemberagreementgui',
      13 => 'ilexportgui',
      14 => 'ilmemberexportgui',
      15 => 'ilcommonactiondispatchergui',
      16 => 'ilobjectservicesettingsgui',
      17 => 'ilsessionoverviewgui',
      18 => 'ilgroupmembershipgui',
      19 => 'ilbadgemanagementgui',
      20 => 'ilmailmembersearchgui',
      21 => 'ilnewstimelinegui',
      22 => 'ilcontainernewssettingsgui',
      23 => 'ilcontainerskillgui',
      24 => 'ilcalendarpresentationgui',
      25 => 'illtiproviderobjectsettinggui',
      26 => 'ilobjectmetadatagui',
      27 => 'ilobjecttranslationgui',
      28 => 'ilpropertyformgui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
      1 => 'iladvancedsearchgui',
      2 => 'illuceneadvancedsearchgui',
      3 => 'illucenesearchgui',
      4 => 'ilrepositorygui',
      5 => 'ilsearchgui',
    ),
  ),
  'ilobjgroupreferencegui' => 
  array (
    'cid' => 'n2',
    'class_name' => 'ilObjGroupReferenceGUI',
    'class_path' => './components/ILIAS/GroupReference/classes/class.ilObjGroupReferenceGUI.php',
    'children' => 
    array (
      0 => 'ilpermissiongui',
      1 => 'ilinfoscreengui',
      2 => 'ilpropertyformgui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
      1 => 'ilrepositorygui',
    ),
  ),
  'ilobjhelpsettingsgui' => 
  array (
    'cid' => 'n4',
    'class_name' => 'ilObjHelpSettingsGUI',
    'class_path' => './Services/Help/Administration/class.ilObjHelpSettingsGUI.php',
    'children' => 
    array (
      0 => 'ilpermissiongui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
    ),
  ),
  'ilobjindividualassessmentgui' => 
  array (
    'cid' => 'n5',
    'class_name' => 'ilObjIndividualAssessmentGUI',
    'class_path' => './components/ILIAS/IndividualAssessment/classes/class.ilObjIndividualAssessmentGUI.php',
    'children' => 
    array (
      0 => 'ilpermissiongui',
      1 => 'ilinfoscreengui',
      2 => 'ilobjectcopygui',
      3 => 'ilcommonactiondispatchergui',
      4 => 'ilindividualassessmentsettingsgui',
      5 => 'ilindividualassessmentmembersgui',
      6 => 'illearningprogressgui',
      7 => 'ilexportgui',
      8 => 'ilobjectmetadatagui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
      1 => 'ilobjlearningsequencegui',
      2 => 'ilrepositorygui',
    ),
  ),
  'ilobjitemgroupgui' => 
  array (
    'cid' => 'n7',
    'class_name' => 'ilObjItemGroupGUI',
    'class_path' => './components/ILIAS/ItemGroup/classes/class.ilObjItemGroupGUI.php',
    'children' => 
    array (
      0 => 'ilpermissiongui',
      1 => 'ildidactictemplategui',
      2 => 'ilcommonactiondispatchergui',
      3 => 'ilobjectcopygui',
      4 => 'ilobjecttranslationgui',
    ),
    'parents' => 
    array (
      0 => 'ilrepositorygui',
      1 => 'iladministrationgui',
    ),
  ),
  'ilobjltiadministrationgui' => 
  array (
    'cid' => 'n9',
    'class_name' => 'ilObjLTIAdministrationGUI',
    'class_path' => './Services/LTI/classes/class.ilObjLTIAdministrationGUI.php',
    'children' => 
    array (
      0 => 'ilpermissiongui',
      1 => 'illticonsumeradministrationgui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
    ),
  ),
  'ilobjlticonsumergui' => 
  array (
    'cid' => 'na',
    'class_name' => 'ilObjLTIConsumerGUI',
    'class_path' => './components/ILIAS/LTIConsumer/classes/class.ilObjLTIConsumerGUI.php',
    'children' => 
    array (
      0 => 'ilobjectcopygui',
      1 => 'ilcommonactiondispatchergui',
      2 => 'ilpermissiongui',
      3 => 'ilobjectmetadatagui',
      4 => 'ilinfoscreengui',
      5 => 'illearningprogressgui',
      6 => 'illticonsumersettingsgui',
      7 => 'illticonsumerxapistatementsgui',
      8 => 'illticonsumerscoringgui',
      9 => 'illticonsumercontentgui',
      10 => 'illticonsumergradesynchronizationgui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
      1 => 'ilrepositorygui',
    ),
  ),
  'ilobjlticonsumerverificationgui' => 
  array (
    'cid' => 'nc',
    'class_name' => 'ilObjLTIConsumerVerificationGUI',
    'class_path' => './components/ILIAS/LTIConsumer/classes/Verification/class.ilObjLTIConsumerVerificationGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilpersonalworkspacegui',
    ),
  ),
  'ilobjlanguageextgui' => 
  array (
    'cid' => 'ne',
    'class_name' => 'ilObjLanguageExtGUI',
    'class_path' => './Services/Language/classes/class.ilObjLanguageExtGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ildashboardgui',
      1 => 'iladministrationgui',
    ),
  ),
  'ilobjlanguagefoldergui' => 
  array (
    'cid' => 'nf',
    'class_name' => 'ilObjLanguageFolderGUI',
    'class_path' => './Services/Language/classes/class.ilObjLanguageFolderGUI.php',
    'children' => 
    array (
      0 => 'ilpermissiongui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
    ),
  ),
  'ilobjlearninghistorysettingsgui' => 
  array (
    'cid' => 'nh',
    'class_name' => 'ilObjLearningHistorySettingsGUI',
    'class_path' => './Services/LearningHistory/Administration/classes/class.ilObjLearningHistorySettingsGUI.php',
    'children' => 
    array (
      0 => 'ilpermissiongui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
    ),
  ),
  'ilobjlearningmodulegui' => 
  array (
    'cid' => 'ni',
    'class_name' => 'ilObjLearningModuleGUI',
    'class_path' => './components/ILIAS/LearningModule/classes/class.ilObjLearningModuleGUI.php',
    'children' => 
    array (
      0 => 'illmpageobjectgui',
      1 => 'ilstructureobjectgui',
      2 => 'ilobjectcontentstylesettingsgui',
      3 => 'ilobjectmetadatagui',
      4 => 'illearningprogressgui',
      5 => 'ilpermissiongui',
      6 => 'ilinfoscreengui',
      7 => 'ilobjectcopygui',
      8 => 'ilexportgui',
      9 => 'ilcommonactiondispatchergui',
      10 => 'ilpagemultilanggui',
      11 => 'ilobjecttranslationgui',
      12 => 'ilmobmultisrtuploadgui',
      13 => 'illmimportgui',
      14 => 'illmeditshorttitlesgui',
      15 => 'illtiproviderobjectsettinggui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
      1 => 'illmeditorgui',
      2 => 'ilobjlearningsequencegui',
      3 => 'ilrepositorygui',
    ),
  ),
  'ilobjlearningresourcessettingsgui' => 
  array (
    'cid' => 'nl',
    'class_name' => 'ilObjLearningResourcesSettingsGUI',
    'class_path' => './components/ILIAS/LearningModule/classes/class.ilObjLearningResourcesSettingsGUI.php',
    'children' => 
    array (
      0 => 'ilpermissiongui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
    ),
  ),
  'ilobjlearningsequenceadmingui' => 
  array (
    'cid' => 'nm',
    'class_name' => 'ilObjLearningSequenceAdminGUI',
    'class_path' => './components/ILIAS/LearningSequence/classes/class.ilObjLearningSequenceAdminGUI.php',
    'children' => 
    array (
      0 => 'ilpermissiongui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
    ),
  ),
  'ilobjlearningsequencecontentgui' => 
  array (
    'cid' => 'nn',
    'class_name' => 'ilObjLearningSequenceContentGUI',
    'class_path' => './components/ILIAS/LearningSequence/classes/Content/class.ilObjLearningSequenceContentGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjlearningsequencegui',
    ),
  ),
  'ilobjlearningsequenceeditextrogui' => 
  array (
    'cid' => 'np',
    'class_name' => 'ilObjLearningSequenceEditExtroGUI',
    'class_path' => './components/ILIAS/LearningSequence/classes/PageEditor/class.ilObjLearningSequenceEditExtroGUI.php',
    'children' => 
    array (
      0 => 'ilpageeditorgui',
      1 => 'ileditclipboardgui',
    ),
    'parents' => 
    array (
      0 => 'ilobjlearningsequencegui',
    ),
  ),
  'ilobjlearningsequenceeditintrogui' => 
  array (
    'cid' => 'nq',
    'class_name' => 'ilObjLearningSequenceEditIntroGUI',
    'class_path' => './components/ILIAS/LearningSequence/classes/PageEditor/class.ilObjLearningSequenceEditIntroGUI.php',
    'children' => 
    array (
      0 => 'ilpageeditorgui',
      1 => 'ileditclipboardgui',
    ),
    'parents' => 
    array (
      0 => 'ilobjlearningsequencegui',
    ),
  ),
  'ilobjlearningsequencegui' => 
  array (
    'cid' => 'nr',
    'class_name' => 'ilObjLearningSequenceGUI',
    'class_path' => './components/ILIAS/LearningSequence/classes/class.ilObjLearningSequenceGUI.php',
    'children' => 
    array (
      0 => 'ilpermissiongui',
      1 => 'ilinfoscreengui',
      2 => 'ilcommonactiondispatchergui',
      3 => 'ilcolumngui',
      4 => 'ilobjectcopygui',
      5 => 'ilexportgui',
      6 => 'ilobjlearningsequencesettingsgui',
      7 => 'ilobjlearningsequencecontentgui',
      8 => 'ilobjlearningsequencelearnergui',
      9 => 'ilobjlearningsequencelppollinggui',
      10 => 'illearningsequencemembershipgui',
      11 => 'illearningprogressgui',
      12 => 'ilobjlearningmodulegui',
      13 => 'ilobjfilebasedlmgui',
      14 => 'ilobjsahslearningmodulegui',
      15 => 'ilobjcontentpagegui',
      16 => 'ilobjexercisegui',
      17 => 'ilobjfilegui',
      18 => 'ilobjindividualassessmentgui',
      19 => 'ilindividualassessmentsettingsgui',
      20 => 'ilobjtestgui',
      21 => 'ilobjsurveygui',
      22 => 'ilobjfileuploadhandlergui',
      23 => 'ilobjlearningsequenceeditintrogui',
      24 => 'ilobjlearningsequenceeditextrogui',
    ),
    'parents' => 
    array (
      0 => 'ilrepositorygui',
      1 => 'iladministrationgui',
    ),
  ),
  'ilobjlearningsequencelppollinggui' => 
  array (
    'cid' => 'ns',
    'class_name' => 'ilObjLearningSequenceLPPollingGUI',
    'class_path' => './components/ILIAS/LearningSequence/classes/Player/class.ilObjLearningSequenceLPPollingGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjlearningsequencegui',
    ),
  ),
  'ilobjlearningsequencelearnergui' => 
  array (
    'cid' => 'nt',
    'class_name' => 'ilObjLearningSequenceLearnerGUI',
    'class_path' => './components/ILIAS/LearningSequence/classes/Player/class.ilObjLearningSequenceLearnerGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjlearningsequencegui',
    ),
  ),
  'ilobjlearningsequencesettingsgui' => 
  array (
    'cid' => 'nv',
    'class_name' => 'ilObjLearningSequenceSettingsGUI',
    'class_path' => './components/ILIAS/LearningSequence/classes/Settings/class.ilObjLearningSequenceSettingsGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjlearningsequencegui',
    ),
  ),
  'ilobjlegaldocumentsgui' => 
  array (
    'cid' => 'nw',
    'class_name' => 'ilObjLegalDocumentsGUI',
    'class_path' => './Services/LegalDocuments/classes/class.ilObjLegalDocumentsGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
      1 => 'ilrepositorygui',
    ),
  ),
  'ilobjlegalnoticegui' => 
  array (
    'cid' => 'nx',
    'class_name' => 'ilObjLegalNoticeGUI',
    'class_path' => './Services/Imprint/classes/class.ilObjLegalNoticeGUI.php',
    'children' => 
    array (
      0 => 'ilpermissiongui',
      1 => 'ilimprintgui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
    ),
  ),
  'ilobjlinkresourcegui' => 
  array (
    'cid' => 'ny',
    'class_name' => 'ilObjLinkResourceGUI',
    'class_path' => './components/ILIAS/WebResource/classes/class.ilObjLinkResourceGUI.php',
    'children' => 
    array (
      0 => 'ilobjectmetadatagui',
      1 => 'ilpermissiongui',
      2 => 'ilinfoscreengui',
      3 => 'ilobjectcopygui',
      4 => 'ilexportgui',
      5 => 'ilworkspaceaccessgui',
      6 => 'ilcommonactiondispatchergui',
      7 => 'ilpropertyformgui',
      8 => 'ilinternallinkgui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
      1 => 'illinkresourcehandlergui',
      2 => 'ilpersonalworkspacegui',
      3 => 'ilrepositorygui',
      4 => 'ilsharedresourcegui',
    ),
  ),
  'ilobjloggingsettingsgui' => 
  array (
    'cid' => 'o1',
    'class_name' => 'ilObjLoggingSettingsGUI',
    'class_path' => './Services/Logging/classes/class.ilObjLoggingSettingsGUI.php',
    'children' => 
    array (
      0 => 'ilpermissiongui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
    ),
  ),
  'ilobjmdsettingsgui' => 
  array (
    'cid' => 'o2',
    'class_name' => 'ilObjMDSettingsGUI',
    'class_path' => './Services/MetaData/classes/Settings/class.ilObjMDSettingsGUI.php',
    'children' => 
    array (
      0 => 'ilpermissiongui',
      1 => 'iladvancedmdsettingsgui',
      2 => 'ilmdcopyrightselectiongui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
    ),
  ),
  'ilobjmailgui' => 
  array (
    'cid' => 'o3',
    'class_name' => 'ilObjMailGUI',
    'class_path' => './Services/Mail/classes/class.ilObjMailGUI.php',
    'children' => 
    array (
      0 => 'ilpermissiongui',
      1 => 'ilmailtemplategui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
    ),
  ),
  'ilobjmainmenugui' => 
  array (
    'cid' => 'o4',
    'class_name' => 'ilObjMainMenuGUI',
    'class_path' => './Services/MainMenu/classes/class.ilObjMainMenuGUI.php',
    'children' => 
    array (
      0 => 'ilpermissiongui',
      1 => 'ilmmsubitemgui',
      2 => 'ilmmtopitemgui',
      3 => 'ilmmuploadhandlergui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
    ),
  ),
  'ilobjmediacastgui' => 
  array (
    'cid' => 'o5',
    'class_name' => 'ilObjMediaCastGUI',
    'class_path' => './components/ILIAS/MediaCast/classes/class.ilObjMediaCastGUI.php',
    'children' => 
    array (
      0 => 'ilpermissiongui',
      1 => 'ilinfoscreengui',
      2 => 'ilexportgui',
      3 => 'ilcommonactiondispatchergui',
      4 => 'ilmediacreationgui',
      5 => 'illearningprogressgui',
      6 => 'ilobjectcopygui',
      7 => 'mcstimagegallerygui',
      8 => 'mcstpodcastgui',
      9 => 'ilcommentgui',
    ),
    'parents' => 
    array (
      0 => 'ilrepositorygui',
      1 => 'iladministrationgui',
      2 => 'ilmediacasthandlergui',
    ),
  ),
  'ilobjmediacastsettingsgui' => 
  array (
    'cid' => 'o7',
    'class_name' => 'ilObjMediaCastSettingsGUI',
    'class_path' => './components/ILIAS/MediaCast/classes/class.ilObjMediaCastSettingsGUI.php',
    'children' => 
    array (
      0 => 'ilpermissiongui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
    ),
  ),
  'ilobjmediaobjectgui' => 
  array (
    'cid' => 'o8',
    'class_name' => 'ilObjMediaObjectGUI',
    'class_path' => './Services/MediaObjects/classes/class.ilObjMediaObjectGUI.php',
    'children' => 
    array (
      0 => 'ilobjectmetadatagui',
      1 => 'ilimagemapeditorgui',
      2 => 'ilfilesystemgui',
    ),
    'parents' => 
    array (
      0 => 'ileditclipboardgui',
      1 => 'ilobjmediapoolgui',
      2 => 'ilpcmediaobjectgui',
      3 => 'ilpageeditorgui',
    ),
  ),
  'ilobjmediaobjectssettingsgui' => 
  array (
    'cid' => 'o9',
    'class_name' => 'ilObjMediaObjectsSettingsGUI',
    'class_path' => './Services/MediaObjects/classes/class.ilObjMediaObjectsSettingsGUI.php',
    'children' => 
    array (
      0 => 'ilpermissiongui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
    ),
  ),
  'ilobjmediapoolgui' => 
  array (
    'cid' => 'oa',
    'class_name' => 'ilObjMediaPoolGUI',
    'class_path' => './components/ILIAS/MediaPool/classes/class.ilObjMediaPoolGUI.php',
    'children' => 
    array (
      0 => 'ilobjmediaobjectgui',
      1 => 'ilobjfoldergui',
      2 => 'ileditclipboardgui',
      3 => 'ilpermissiongui',
      4 => 'ilinfoscreengui',
      5 => 'ilmediapoolpagegui',
      6 => 'ilexportgui',
      7 => 'ilfilesystemgui',
      8 => 'ilcommonactiondispatchergui',
      9 => 'ilobjectcopygui',
      10 => 'ilobjecttranslationgui',
      11 => 'ilmediapoolimportgui',
      12 => 'ilmobmultisrtuploadgui',
      13 => 'ilobjectmetadatagui',
      14 => 'ilrepostandarduploadhandlergui',
      15 => 'ilmediacreationgui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
      1 => 'ilmediapoolpresentationgui',
      2 => 'ilrepositorygui',
    ),
  ),
  'ilobjnewssettingsgui' => 
  array (
    'cid' => 'od',
    'class_name' => 'ilObjNewsSettingsGUI',
    'class_path' => './Services/News/classes/class.ilObjNewsSettingsGUI.php',
    'children' => 
    array (
      0 => 'ilpermissiongui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
    ),
  ),
  'ilobjnotessettingsgui' => 
  array (
    'cid' => 'oe',
    'class_name' => 'ilObjNotesSettingsGUI',
    'class_path' => './Services/Notes/Administration/classes/class.ilObjNotesSettingsGUI.php',
    'children' => 
    array (
      0 => 'ilpermissiongui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
    ),
  ),
  'ilobjnotificationadmingui' => 
  array (
    'cid' => 'of',
    'class_name' => 'ilObjNotificationAdminGUI',
    'class_path' => './Services/Notifications/classes/class.ilObjNotificationAdminGUI.php',
    'children' => 
    array (
      0 => 'ilpermissiongui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
    ),
  ),
  'ilobjnotificationsettingsgui' => 
  array (
    'cid' => 'og',
    'class_name' => 'ilObjNotificationSettingsGUI',
    'class_path' => './Services/Notification/classes/class.ilObjNotificationSettingsGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjbloggui',
      1 => 'ilobjwikigui',
    ),
  ),
  'ilobjobjectfoldergui' => 
  array (
    'cid' => 'oh',
    'class_name' => 'ilObjObjectFolderGUI',
    'class_path' => './Services/Object/classes/class.ilObjObjectFolderGUI.php',
    'children' => 
    array (
      0 => 'ilpermissiongui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
    ),
  ),
  'ilobjobjecttemplateadministrationgui' => 
  array (
    'cid' => 'oi',
    'class_name' => 'ilObjObjectTemplateAdministrationGUI',
    'class_path' => './Services/DidacticTemplate/classes/class.ilObjObjectTemplateAdministrationGUI.php',
    'children' => 
    array (
      0 => 'ilpermissiongui',
      1 => 'ildidactictemplatesettingsgui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
    ),
  ),
  'ilobjorgunitgui' => 
  array (
    'cid' => 'oj',
    'class_name' => 'ilObjOrgUnitGUI',
    'class_path' => './components/ILIAS/OrgUnit/classes/class.ilObjOrgUnitGUI.php',
    'children' => 
    array (
      0 => 'ilpermissiongui',
      1 => 'ilpageobjectgui',
      2 => 'ilobjusergui',
      3 => 'ilobjuserfoldergui',
      4 => 'ilinfoscreengui',
      5 => 'ilobjstylesheetgui',
      6 => 'ilcommonactiondispatchergui',
      7 => 'ilcolumngui',
      8 => 'ilobjectcopygui',
      9 => 'ilusertablegui',
      10 => 'ildidactictemplategui',
      11 => 'illearningprogressgui',
      12 => 'iltranslationgui',
      13 => 'illocalusergui',
      14 => 'ilorgunitexportgui',
      15 => 'ilextidgui',
      16 => 'ilorgunitsimpleimportgui',
      17 => 'ilorgunitsimpleuserimportgui',
      18 => 'ilorgunittypegui',
      19 => 'ilorgunitpositiongui',
      20 => 'ilorgunituserassignmentgui',
      21 => 'ilorgunittypegui',
      22 => 'ilpropertyformgui',
      23 => 'ilorgunitglobalsettingsgui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
      1 => 'ilobjplugindispatchgui',
    ),
  ),
  'ilobjpersonalworkspacesettingsgui' => 
  array (
    'cid' => 'on',
    'class_name' => 'ilObjPersonalWorkspaceSettingsGUI',
    'class_path' => './Services/PersonalWorkspace/Administration/classes/class.ilObjPersonalWorkspaceSettingsGUI.php',
    'children' => 
    array (
      0 => 'ilpermissiongui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
    ),
  ),
  'ilobjplugindispatchgui' => 
  array (
    'cid' => 'oo',
    'class_name' => 'ilObjPluginDispatchGUI',
    'class_path' => './Services/Repository/PluginSlot/class.ilObjPluginDispatchGUI.php',
    'children' => 
    array (
      0 => 'ilobjorgunitgui',
    ),
    'parents' => 
    array (
    ),
  ),
  'ilobjpollgui' => 
  array (
    'cid' => 'op',
    'class_name' => 'ilObjPollGUI',
    'class_path' => './components/ILIAS/Poll/classes/class.ilObjPollGUI.php',
    'children' => 
    array (
      0 => 'ilinfoscreengui',
      1 => 'ilnotegui',
      2 => 'ilcommonactiondispatchergui',
      3 => 'ilpermissiongui',
      4 => 'ilobjectcopygui',
      5 => 'ilexportgui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
      1 => 'ilrepositorygui',
    ),
  ),
  'ilobjportfolioadministrationgui' => 
  array (
    'cid' => 'or',
    'class_name' => 'ilObjPortfolioAdministrationGUI',
    'class_path' => './components/ILIAS/Portfolio/Administration/class.ilObjPortfolioAdministrationGUI.php',
    'children' => 
    array (
      0 => 'ilpermissiongui',
      1 => 'ilportfolioroleassignmentgui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
    ),
  ),
  'ilobjportfoliogui' => 
  array (
    'cid' => 'ot',
    'class_name' => 'ilObjPortfolioGUI',
    'class_path' => './components/ILIAS/Portfolio/classes/class.ilObjPortfolioGUI.php',
    'children' => 
    array (
      0 => 'ilportfoliopagegui',
      1 => 'ilpageobjectgui',
      2 => 'ilworkspaceaccessgui',
      3 => 'ilnotegui',
      4 => 'ilcommonactiondispatchergui',
      5 => 'ilobjectcontentstylesettingsgui',
      6 => 'ilportfolioexercisegui',
    ),
    'parents' => 
    array (
      0 => 'ilportfoliorepositorygui',
      1 => 'ilpublicuserprofilegui',
      2 => 'ilsharedresourcegui',
    ),
  ),
  'ilobjportfoliotemplategui' => 
  array (
    'cid' => 'ou',
    'class_name' => 'ilObjPortfolioTemplateGUI',
    'class_path' => './components/ILIAS/Portfolio/Template/class.ilObjPortfolioTemplateGUI.php',
    'children' => 
    array (
      0 => 'ilportfoliotemplatepagegui',
      1 => 'ilpageobjectgui',
      2 => 'ilnotegui',
      3 => 'ilobjectcopygui',
      4 => 'ilinfoscreengui',
      5 => 'ilcommonactiondispatchergui',
      6 => 'ilpermissiongui',
      7 => 'ilexportgui',
      8 => 'ilobjectcontentstylesettingsgui',
      9 => 'ilobjectmetadatagui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
      1 => 'ilrepositorygui',
    ),
  ),
  'ilobjprivacysecuritygui' => 
  array (
    'cid' => 'ow',
    'class_name' => 'ilObjPrivacySecurityGUI',
    'class_path' => './Services/PrivacySecurity/classes/class.ilObjPrivacySecurityGUI.php',
    'children' => 
    array (
      0 => 'ilpermissiongui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
    ),
  ),
  'ilobjquestionpoolgui' => 
  array (
    'cid' => 'ox',
    'class_name' => 'ilObjQuestionPoolGUI',
    'class_path' => './components/ILIAS/TestQuestionPool/classes/class.ilObjQuestionPoolGUI.php',
    'children' => 
    array (
      0 => 'ilassquestionpagegui',
      1 => 'ilquestionbrowsertablegui',
      2 => 'iltoolbargui',
      3 => 'ilobjtestgui',
      4 => 'assmultiplechoicegui',
      5 => 'assclozetestgui',
      6 => 'assmatchingquestiongui',
      7 => 'assorderingquestiongui',
      8 => 'assimagemapquestiongui',
      9 => 'assnumericgui',
      10 => 'asstextsubsetgui',
      11 => 'asssinglechoicegui',
      12 => 'ilpropertyformgui',
      13 => 'asstextquestiongui',
      14 => 'ilobjectmetadatagui',
      15 => 'ilpermissiongui',
      16 => 'ilobjectcopygui',
      17 => 'ilquestionpoolexportgui',
      18 => 'ilinfoscreengui',
      19 => 'iltaxonomysettingsgui',
      20 => 'ilcommonactiondispatchergui',
      21 => 'ilassquestionhintsgui',
      22 => 'ilassquestionfeedbackeditinggui',
      23 => 'illocalunitconfigurationgui',
      24 => 'ilobjquestionpoolsettingsgeneralgui',
      25 => 'assformulaquestiongui',
      26 => 'ilassquestionpreviewgui',
      27 => 'asskprimchoicegui',
      28 => 'asslongmenugui',
      29 => 'ilquestionpoolskilladministrationgui',
      30 => 'asserrortextgui',
      31 => 'assfileuploadgui',
      32 => 'assorderinghorizontalgui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
      1 => 'ilobjtestgui',
      2 => 'ilrepositorygui',
      3 => 'iltestexpresspageobjectgui',
    ),
  ),
  'ilobjquestionpoolsettingsgeneralgui' => 
  array (
    'cid' => 'oz',
    'class_name' => 'ilObjQuestionPoolSettingsGeneralGUI',
    'class_path' => './components/ILIAS/TestQuestionPool/classes/class.ilObjQuestionPoolSettingsGeneralGUI.php',
    'children' => 
    array (
      0 => 'ilpropertyformgui',
    ),
    'parents' => 
    array (
      0 => 'ilobjquestionpoolgui',
    ),
  ),
  'ilobjrecoveryfoldergui' => 
  array (
    'cid' => 'p0',
    'class_name' => 'ilObjRecoveryFolderGUI',
    'class_path' => './Services/Administration/classes/class.ilObjRecoveryFolderGUI.php',
    'children' => 
    array (
      0 => 'ilpermissiongui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
    ),
  ),
  'ilobjremotecategorygui' => 
  array (
    'cid' => 'p2',
    'class_name' => 'ilObjRemoteCategoryGUI',
    'class_path' => './components/ILIAS/RemoteCategory/classes/class.ilObjRemoteCategoryGUI.php',
    'children' => 
    array (
      0 => 'ilpermissiongui',
      1 => 'ilinfoscreengui',
      2 => 'ilcommonactiondispatchergui',
      3 => 'ilecsuserconsentmodalgui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
      1 => 'ilrepositorygui',
    ),
  ),
  'ilobjremotecoursegui' => 
  array (
    'cid' => 'p4',
    'class_name' => 'ilObjRemoteCourseGUI',
    'class_path' => './components/ILIAS/RemoteCourse/classes/class.ilObjRemoteCourseGUI.php',
    'children' => 
    array (
      0 => 'ilpermissiongui',
      1 => 'ilinfoscreengui',
      2 => 'ilcommonactiondispatchergui',
      3 => 'ilecsuserconsentmodalgui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
      1 => 'ilrepositorygui',
    ),
  ),
  'ilobjremotefilegui' => 
  array (
    'cid' => 'p6',
    'class_name' => 'ilObjRemoteFileGUI',
    'class_path' => './components/ILIAS/RemoteFile/classes/class.ilObjRemoteFileGUI.php',
    'children' => 
    array (
      0 => 'ilpermissiongui',
      1 => 'ilinfoscreengui',
      2 => 'ilcommonactiondispatchergui',
      3 => 'ilecsuserconsentmodalgui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
      1 => 'ilrepositorygui',
    ),
  ),
  'ilobjremoteglossarygui' => 
  array (
    'cid' => 'p8',
    'class_name' => 'ilObjRemoteGlossaryGUI',
    'class_path' => './components/ILIAS/RemoteGlossary/classes/class.ilObjRemoteGlossaryGUI.php',
    'children' => 
    array (
      0 => 'ilpermissiongui',
      1 => 'ilinfoscreengui',
      2 => 'ilcommonactiondispatchergui',
      3 => 'ilecsuserconsentmodalgui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
      1 => 'ilrepositorygui',
    ),
  ),
  'ilobjremotegroupgui' => 
  array (
    'cid' => 'pa',
    'class_name' => 'ilObjRemoteGroupGUI',
    'class_path' => './components/ILIAS/RemoteGroup/classes/class.ilObjRemoteGroupGUI.php',
    'children' => 
    array (
      0 => 'ilpermissiongui',
      1 => 'ilinfoscreengui',
      2 => 'ilcommonactiondispatchergui',
      3 => 'ilecsuserconsentmodalgui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
      1 => 'ilrepositorygui',
    ),
  ),
  'ilobjremotelearningmodulegui' => 
  array (
    'cid' => 'pc',
    'class_name' => 'ilObjRemoteLearningModuleGUI',
    'class_path' => './components/ILIAS/RemoteLearningModule/classes/class.ilObjRemoteLearningModuleGUI.php',
    'children' => 
    array (
      0 => 'ilpermissiongui',
      1 => 'ilinfoscreengui',
      2 => 'ilcommonactiondispatchergui',
      3 => 'ilecsuserconsentmodalgui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
      1 => 'ilrepositorygui',
    ),
  ),
  'ilobjremotetestgui' => 
  array (
    'cid' => 'pe',
    'class_name' => 'ilObjRemoteTestGUI',
    'class_path' => './components/ILIAS/RemoteTest/classes/class.ilObjRemoteTestGUI.php',
    'children' => 
    array (
      0 => 'ilpermissiongui',
      1 => 'ilinfoscreengui',
      2 => 'ilcommonactiondispatchergui',
      3 => 'ilecsuserconsentmodalgui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
      1 => 'ilrepositorygui',
    ),
  ),
  'ilobjremotewikigui' => 
  array (
    'cid' => 'pg',
    'class_name' => 'ilObjRemoteWikiGUI',
    'class_path' => './components/ILIAS/RemoteWiki/classes/class.ilObjRemoteWikiGUI.php',
    'children' => 
    array (
      0 => 'ilpermissiongui',
      1 => 'ilinfoscreengui',
      2 => 'ilcommonactiondispatchergui',
      3 => 'ilecsuserconsentmodalgui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
      1 => 'ilrepositorygui',
    ),
  ),
  'ilobjrepositorysettingsgui' => 
  array (
    'cid' => 'pi',
    'class_name' => 'ilObjRepositorySettingsGUI',
    'class_path' => './Services/Repository/Administration/class.ilObjRepositorySettingsGUI.php',
    'children' => 
    array (
      0 => 'ilpermissiongui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
    ),
  ),
  'ilobjrolefoldergui' => 
  array (
    'cid' => 'pj',
    'class_name' => 'ilObjRoleFolderGUI',
    'class_path' => './Services/AccessControl/classes/class.ilObjRoleFolderGUI.php',
    'children' => 
    array (
      0 => 'ilpermissiongui',
      1 => 'ildidactictemplatesettingsgui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
    ),
  ),
  'ilobjrolegui' => 
  array (
    'cid' => 'pk',
    'class_name' => 'ilObjRoleGUI',
    'class_path' => './Services/AccessControl/classes/class.ilObjRoleGUI.php',
    'children' => 
    array (
      0 => 'ilrepositorysearchgui',
      1 => 'ilexportgui',
      2 => 'ilrecommendedcontentroleconfiggui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
      1 => 'ilpermissiongui',
      2 => 'ilrepositorygui',
    ),
  ),
  'ilobjroletemplategui' => 
  array (
    'cid' => 'pl',
    'class_name' => 'ilObjRoleTemplateGUI',
    'class_path' => './Services/AccessControl/classes/class.ilObjRoleTemplateGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
    ),
  ),
  'ilobjrootfoldergui' => 
  array (
    'cid' => 'pn',
    'class_name' => 'ilObjRootFolderGUI',
    'class_path' => './components/ILIAS/RootFolder/classes/class.ilObjRootFolderGUI.php',
    'children' => 
    array (
      0 => 'ilpermissiongui',
      1 => 'ilcontainerpagegui',
      2 => 'ilcolumngui',
      3 => 'ilobjectcopygui',
      4 => 'ilobjectcontentstylesettingsgui',
      5 => 'ilcommonactiondispatchergui',
      6 => 'ilobjecttranslationgui',
      7 => 'ilrepositorytrashgui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
      1 => 'iladvancedsearchgui',
      2 => 'ilcommonactiondispatchergui',
      3 => 'illuceneadvancedsearchgui',
      4 => 'illucenesearchgui',
      5 => 'ilrepositorygui',
      6 => 'ilsearchgui',
    ),
  ),
  'ilobjsahslearningmodulegui' => 
  array (
    'cid' => 'pp',
    'class_name' => 'ilObjSAHSLearningModuleGUI',
    'class_path' => './components/ILIAS/ScormAicc/classes/class.ilObjSAHSLearningModuleGUI.php',
    'children' => 
    array (
      0 => 'ilfilesystemgui',
      1 => 'ilobjectmetadatagui',
      2 => 'ilpermissiongui',
      3 => 'ilinfoscreengui',
      4 => 'illearningprogressgui',
      5 => 'ilcommonactiondispatchergui',
      6 => 'ilexportgui',
      7 => 'ilobjectcopygui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
      1 => 'ilobjlearningsequencegui',
      2 => 'ilrepositorygui',
      3 => 'ilsahseditgui',
    ),
  ),
  'ilobjscorm2004learningmodulegui' => 
  array (
    'cid' => 'pr',
    'class_name' => 'ilObjSCORM2004LearningModuleGUI',
    'class_path' => './components/ILIAS/Scorm2004/classes/class.ilObjSCORM2004LearningModuleGUI.php',
    'children' => 
    array (
      0 => 'ilfilesystemgui',
      1 => 'ilobjectmetadatagui',
      2 => 'ilpermissiongui',
      3 => 'illearningprogressgui',
      4 => 'ilinfoscreengui',
      5 => 'ilscorm2004seqchaptergui',
      6 => 'ilscorm2004scogui',
      7 => 'ilobjstylesheetgui',
      8 => 'ilscorm2004assetgui',
      9 => 'ilcommonactiondispatchergui',
      10 => 'ilscorm2004trackingitemsperscofiltergui',
      11 => 'ilscorm2004trackingitemsperuserfiltergui',
      12 => 'illtiproviderobjectsettinggui',
      13 => 'ilscorm2004trackingitemstablegui',
    ),
    'parents' => 
    array (
      0 => 'ilsahseditgui',
      1 => 'ilsahspresentationgui',
    ),
  ),
  'ilobjscormlearningmodulegui' => 
  array (
    'cid' => 'ps',
    'class_name' => 'ilObjSCORMLearningModuleGUI',
    'class_path' => './components/ILIAS/ScormAicc/classes/class.ilObjSCORMLearningModuleGUI.php',
    'children' => 
    array (
      0 => 'ilfilesystemgui',
      1 => 'ilobjectmetadatagui',
      2 => 'ilpermissiongui',
      3 => 'illearningprogressgui',
      4 => 'ilinfoscreengui',
      5 => 'ilcertificategui',
      6 => 'ilscormtrackingitemsperscofiltergui',
      7 => 'ilscormtrackingitemsperuserfiltergui',
      8 => 'ilscormtrackingitemstablegui',
      9 => 'illtiproviderobjectsettinggui',
    ),
    'parents' => 
    array (
      0 => 'ilsahseditgui',
      1 => 'ilsahspresentationgui',
    ),
  ),
  'ilobjscormverificationgui' => 
  array (
    'cid' => 'pt',
    'class_name' => 'ilObjSCORMVerificationGUI',
    'class_path' => './components/ILIAS/ScormAicc/classes/Verification/class.ilObjSCORMVerificationGUI.php',
    'children' => 
    array (
      0 => 'ilworkspaceaccessgui',
    ),
    'parents' => 
    array (
      0 => 'ilpersonalworkspacegui',
    ),
  ),
  'ilobjsearchlucenesettingsformgui' => 
  array (
    'cid' => 'pv',
    'class_name' => 'ilObjSearchLuceneSettingsFormGUI',
    'class_path' => './Services/Search/classes/ObjGUI/class.ilObjSearchLuceneSettingsFormGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjsearchsettingsgui',
    ),
  ),
  'ilobjsearchsettingsformgui' => 
  array (
    'cid' => 'pw',
    'class_name' => 'ilObjSearchSettingsFormGUI',
    'class_path' => './Services/Search/classes/ObjGUI/class.ilObjSearchSettingsFormGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjsearchsettingsgui',
    ),
  ),
  'ilobjsearchsettingsgui' => 
  array (
    'cid' => 'px',
    'class_name' => 'ilObjSearchSettingsGUI',
    'class_path' => './Services/Search/classes/ObjGUI/class.ilObjSearchSettingsGUI.php',
    'children' => 
    array (
      0 => 'ilpermissiongui',
      1 => 'ilobjsearchsettingsformgui',
      2 => 'ilobjsearchlucenesettingsformgui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
    ),
  ),
  'ilobjsessiongui' => 
  array (
    'cid' => 'py',
    'class_name' => 'ilObjSessionGUI',
    'class_path' => './components/ILIAS/Session/classes/class.ilObjSessionGUI.php',
    'children' => 
    array (
      0 => 'ilpermissiongui',
      1 => 'ilinfoscreengui',
      2 => 'ilobjectcopygui',
      3 => 'ilexportgui',
      4 => 'ilcommonactiondispatchergui',
      5 => 'ilmembershipmailgui',
      6 => 'illearningprogressgui',
      7 => 'ilsessionmembershipgui',
      8 => 'ilobjectmetadatagui',
      9 => 'ilpropertyformgui',
      10 => 'ilbookinggatewaygui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
      1 => 'ilrepositorygui',
    ),
  ),
  'ilobjskillmanagementgui' => 
  array (
    'cid' => 'q0',
    'class_name' => 'ilObjSkillManagementGUI',
    'class_path' => './Services/Skill/classes/class.ilObjSkillManagementGUI.php',
    'children' => 
    array (
      0 => 'ilpermissiongui',
      1 => 'skilltreeadmingui',
      2 => 'ilbasicskillgui',
      3 => 'ilbasicskilltemplategui',
      4 => 'ilskillcategorygui',
      5 => 'ilskillrootgui',
      6 => 'ilskilltemplatecategorygui',
      7 => 'ilskilltemplatereferencegui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
    ),
  ),
  'ilobjskilltreegui' => 
  array (
    'cid' => 'q1',
    'class_name' => 'ilObjSkillTreeGUI',
    'class_path' => './Services/Skill/Tree/class.ilObjSkillTreeGUI.php',
    'children' => 
    array (
      0 => 'ilpermissiongui',
      1 => 'ilskillprofilegui',
      2 => 'ilexportgui',
      3 => 'ilbasicskillgui',
      4 => 'ilbasicskilltemplategui',
      5 => 'ilskillcategorygui',
      6 => 'ilskillprofileuploadhandlergui',
      7 => 'ilskillrootgui',
      8 => 'ilskilltemplatecategorygui',
      9 => 'ilskilltemplatereferencegui',
    ),
    'parents' => 
    array (
      0 => 'skilltreeadmingui',
    ),
  ),
  'ilobjstudyprogrammeadmingui' => 
  array (
    'cid' => 'q2',
    'class_name' => 'ilObjStudyProgrammeAdminGUI',
    'class_path' => './components/ILIAS/StudyProgramme/classes/class.ilObjStudyProgrammeAdminGUI.php',
    'children' => 
    array (
      0 => 'ilstudyprogrammetypegui',
      1 => 'ilpermissiongui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
    ),
  ),
  'ilobjstudyprogrammeautocategoriesgui' => 
  array (
    'cid' => 'q3',
    'class_name' => 'ilObjStudyProgrammeAutoCategoriesGUI',
    'class_path' => './components/ILIAS/StudyProgramme/classes/class.ilObjStudyProgrammeAutoCategoriesGUI.php',
    'children' => 
    array (
      0 => 'ilpropertyformgui',
    ),
    'parents' => 
    array (
      0 => 'ilobjstudyprogrammegui',
    ),
  ),
  'ilobjstudyprogrammeautomembershipsgui' => 
  array (
    'cid' => 'q4',
    'class_name' => 'ilObjStudyProgrammeAutoMembershipsGUI',
    'class_path' => './components/ILIAS/StudyProgramme/classes/class.ilObjStudyProgrammeAutoMembershipsGUI.php',
    'children' => 
    array (
      0 => 'ilpropertyformgui',
      1 => 'ilformpropertydispatchgui',
    ),
    'parents' => 
    array (
      0 => 'ilobjstudyprogrammegui',
    ),
  ),
  'ilobjstudyprogrammegui' => 
  array (
    'cid' => 'q5',
    'class_name' => 'ilObjStudyProgrammeGUI',
    'class_path' => './components/ILIAS/StudyProgramme/classes/class.ilObjStudyProgrammeGUI.php',
    'children' => 
    array (
      0 => 'ilpermissiongui',
      1 => 'ilinfoscreengui',
      2 => 'ilcommonactiondispatchergui',
      3 => 'ilcolumngui',
      4 => 'ilobjstudyprogrammesettingsgui',
      5 => 'ilobjstudyprogrammetreegui',
      6 => 'ilobjstudyprogrammemembersgui',
      7 => 'ilobjstudyprogrammeautomembershipsgui',
      8 => 'ilobjectcopygui',
      9 => 'ilobjecttranslationgui',
      10 => 'ilcertificategui',
      11 => 'ilobjstudyprogrammeautocategoriesgui',
      12 => 'ilcontainergui',
      13 => 'ilcontainerpagegui',
      14 => 'ilobjstylesheetgui',
      15 => 'ilobjectcontentstylesettingsgui',
      16 => 'ilprgpageobjectgui',
      17 => 'ilprgmembersexportgui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
      1 => 'ildashboardgui',
      2 => 'ilrepositorygui',
    ),
  ),
  'ilobjstudyprogrammeindividualplangui' => 
  array (
    'cid' => 'q6',
    'class_name' => 'ilObjStudyProgrammeIndividualPlanGUI',
    'class_path' => './components/ILIAS/StudyProgramme/classes/class.ilObjStudyProgrammeIndividualPlanGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjstudyprogrammemembersgui',
    ),
  ),
  'ilobjstudyprogrammemembersgui' => 
  array (
    'cid' => 'q8',
    'class_name' => 'ilObjStudyProgrammeMembersGUI',
    'class_path' => './components/ILIAS/StudyProgramme/classes/class.ilObjStudyProgrammeMembersGUI.php',
    'children' => 
    array (
      0 => 'ilstudyprogrammerepositorysearchgui',
      1 => 'ilobjstudyprogrammeindividualplangui',
      2 => 'ilobjfilegui',
      3 => 'ilstudyprogrammemailmembersearchgui',
      4 => 'ilstudyprogrammechangeexpiredategui',
      5 => 'ilstudyprogrammechangedeadlinegui',
      6 => 'ilformpropertydispatchgui',
    ),
    'parents' => 
    array (
      0 => 'ilobjstudyprogrammegui',
    ),
  ),
  'ilobjstudyprogrammereferencegui' => 
  array (
    'cid' => 'q9',
    'class_name' => 'ilObjStudyProgrammeReferenceGUI',
    'class_path' => './components/ILIAS/StudyProgrammeReference/classes/class.ilObjStudyProgrammeReferenceGUI.php',
    'children' => 
    array (
      0 => 'ilpermissiongui',
      1 => 'ilinfoscreengui',
      2 => 'ilpropertyformgui',
    ),
    'parents' => 
    array (
      0 => 'ilrepositorygui',
    ),
  ),
  'ilobjstudyprogrammesettingsgui' => 
  array (
    'cid' => 'qb',
    'class_name' => 'ilObjStudyProgrammeSettingsGUI',
    'class_path' => './components/ILIAS/StudyProgramme/classes/class.ilObjStudyProgrammeSettingsGUI.php',
    'children' => 
    array (
      0 => 'ilstudyprogrammecommonsettingsgui',
    ),
    'parents' => 
    array (
      0 => 'ilobjstudyprogrammegui',
    ),
  ),
  'ilobjstudyprogrammetreegui' => 
  array (
    'cid' => 'qd',
    'class_name' => 'ilObjStudyProgrammeTreeGUI',
    'class_path' => './components/ILIAS/StudyProgramme/classes/class.ilObjStudyProgrammeTreeGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjstudyprogrammegui',
    ),
  ),
  'ilobjstylesettingsgui' => 
  array (
    'cid' => 'qe',
    'class_name' => 'ilObjStyleSettingsGUI',
    'class_path' => './Services/Style/classes/class.ilObjStyleSettingsGUI.php',
    'children' => 
    array (
      0 => 'ilpermissiongui',
      1 => 'ilsystemstylemaingui',
      2 => 'ilcontentstylesettingsgui',
      3 => 'ilpagelayoutadministrationgui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
    ),
  ),
  'ilobjstylesheetgui' => 
  array (
    'cid' => 'qf',
    'class_name' => 'ilObjStyleSheetGUI',
    'class_path' => './Services/Style/Content/classes/class.ilObjStyleSheetGUI.php',
    'children' => 
    array (
      0 => 'ilexportgui',
      1 => 'ilstylecharacteristicgui',
      2 => 'ilcontentstyleimagegui',
    ),
    'parents' => 
    array (
      0 => 'ilcontentstylesettingsgui',
      1 => 'ilobjcategorygui',
      2 => 'ilobjorgunitgui',
      3 => 'ilobjscorm2004learningmodulegui',
      4 => 'ilobjstudyprogrammegui',
      5 => 'ilobjectcontentstylesettingsgui',
    ),
  ),
  'ilobjsurveyadministrationgui' => 
  array (
    'cid' => 'qh',
    'class_name' => 'ilObjSurveyAdministrationGUI',
    'class_path' => './components/ILIAS/Survey/Administration/class.ilObjSurveyAdministrationGUI.php',
    'children' => 
    array (
      0 => 'ilpermissiongui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
    ),
  ),
  'ilobjsurveygui' => 
  array (
    'cid' => 'qi',
    'class_name' => 'ilObjSurveyGUI',
    'class_path' => './components/ILIAS/Survey/classes/class.ilObjSurveyGUI.php',
    'children' => 
    array (
      0 => 'ilsurveyevaluationgui',
      1 => 'ilsurveyexecutiongui',
      2 => 'ilobjectmetadatagui',
      3 => 'ilpermissiongui',
      4 => 'ilinfoscreengui',
      5 => 'ilobjectcopygui',
      6 => 'ilsurveyskilldeterminationgui',
      7 => 'ilcommonactiondispatchergui',
      8 => 'ilsurveyskillgui',
      9 => 'ilsurveyeditorgui',
      10 => 'ilsurveyconstraintsgui',
      11 => 'ilsurveyparticipantsgui',
      12 => 'illearningprogressgui',
      13 => 'ilexportgui',
      14 => 'illtiproviderobjectsettinggui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
      1 => 'ilobjlearningsequencegui',
      2 => 'ilrepositorygui',
    ),
  ),
  'ilobjsurveyquestionpoolgui' => 
  array (
    'cid' => 'qk',
    'class_name' => 'ilObjSurveyQuestionPoolGUI',
    'class_path' => './components/ILIAS/SurveyQuestionPool/classes/class.ilObjSurveyQuestionPoolGUI.php',
    'children' => 
    array (
      0 => 'surveymultiplechoicequestiongui',
      1 => 'surveymetricquestiongui',
      2 => 'surveysinglechoicequestiongui',
      3 => 'surveytextquestiongui',
      4 => 'surveymatrixquestiongui',
      5 => 'ilinfoscreengui',
      6 => 'ilobjectmetadatagui',
      7 => 'ilpermissiongui',
      8 => 'ilobjectcopygui',
      9 => 'ilcommonactiondispatchergui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
      1 => 'ilrepositorygui',
    ),
  ),
  'ilobjsystemcheckgui' => 
  array (
    'cid' => 'qm',
    'class_name' => 'ilObjSystemCheckGUI',
    'class_path' => './Services/SystemCheck/classes/class.ilObjSystemCheckGUI.php',
    'children' => 
    array (
      0 => 'ilpermissiongui',
      1 => 'ilobjectownershipmanagementgui',
      2 => 'ilobjsystemfoldergui',
      3 => 'ilsctreetasksgui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
    ),
  ),
  'ilobjsystemfoldergui' => 
  array (
    'cid' => 'qn',
    'class_name' => 'ilObjSystemFolderGUI',
    'class_path' => './components/ILIAS/SystemFolder/classes/class.ilObjSystemFolderGUI.php',
    'children' => 
    array (
      0 => 'ilpermissiongui',
      1 => 'ilobjectownershipmanagementgui',
      2 => 'ilcronmanagergui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
      1 => 'ilobjsystemcheckgui',
    ),
  ),
  'ilobjtaggingsettingsgui' => 
  array (
    'cid' => 'qo',
    'class_name' => 'ilObjTaggingSettingsGUI',
    'class_path' => './Services/Tagging/classes/class.ilObjTaggingSettingsGUI.php',
    'children' => 
    array (
      0 => 'ilpermissiongui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
    ),
  ),
  'ilobjtalktemplateadministrationgui' => 
  array (
    'cid' => 'qp',
    'class_name' => 'ilObjTalkTemplateAdministrationGUI',
    'class_path' => './components/ILIAS/EmployeeTalk/classes/class.ilObjTalkTemplateAdministrationGUI.php',
    'children' => 
    array (
      0 => 'ilcommonactiondispatchergui',
      1 => 'ilcolumngui',
      2 => 'ilobjectcopygui',
      3 => 'ilusertablegui',
      4 => 'ilpermissiongui',
      5 => 'ilinfoscreengui',
      6 => 'ilobjtalktemplategui',
      7 => 'ilobjemployeetalkseriesgui',
      8 => 'ilobjtalktemplateadministrationlistgui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
    ),
  ),
  'ilobjtalktemplateadministrationlistgui' => 
  array (
    'cid' => 'qq',
    'class_name' => 'ilObjTalkTemplateAdministrationListGUI',
    'class_path' => './components/ILIAS/EmployeeTalk/classes/class.ilObjTalkTemplateAdministrationListGUI.php',
    'children' => 
    array (
      0 => 'ilformpropertydispatchgui',
    ),
    'parents' => 
    array (
      0 => 'ilobjtalktemplateadministrationgui',
    ),
  ),
  'ilobjtalktemplategui' => 
  array (
    'cid' => 'qr',
    'class_name' => 'ilObjTalkTemplateGUI',
    'class_path' => './components/ILIAS/EmployeeTalk/classes/class.ilObjTalkTemplateGUI.php',
    'children' => 
    array (
      0 => 'ilcommonactiondispatchergui',
      1 => 'ilcolumngui',
      2 => 'ilobjectcopygui',
      3 => 'ilusertablegui',
      4 => 'ilpermissiongui',
      5 => 'ilinfoscreengui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
      1 => 'ilobjtalktemplateadministrationgui',
    ),
  ),
  'ilobjtaxonomyadministrationgui' => 
  array (
    'cid' => 'qt',
    'class_name' => 'ilObjTaxonomyAdministrationGUI',
    'class_path' => './Services/Taxonomy/classes/class.ilObjTaxonomyAdministrationGUI.php',
    'children' => 
    array (
      0 => 'ilpermissiongui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
    ),
  ),
  'ilobjtaxonomygui' => 
  array (
    'cid' => 'qu',
    'class_name' => 'ilObjTaxonomyGUI',
    'class_path' => './Services/Taxonomy/classes/class.ilObjTaxonomyGUI.php',
    'children' => 
    array (
      0 => 'ilobjtaxonomygui',
    ),
    'parents' => 
    array (
      0 => 'ilobjtaxonomygui',
      1 => 'ilobjectmetadatagui',
      2 => 'iltaxonomysettingsgui',
    ),
  ),
  'ilobjtermsofservicegui' => 
  array (
    'cid' => 'qv',
    'class_name' => 'ilObjTermsOfServiceGUI',
    'class_path' => './Services/TermsOfService/classes/class.ilObjTermsOfServiceGUI.php',
    'children' => 
    array (
      0 => 'ilpermissiongui',
      1 => 'iltermsofserviceacceptancehistorygui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
    ),
  ),
  'ilobjtestgui' => 
  array (
    'cid' => 'qw',
    'class_name' => 'ilObjTestGUI',
    'class_path' => './components/ILIAS/Test/classes/class.ilObjTestGUI.php',
    'children' => 
    array (
      0 => 'ilobjcoursegui',
      1 => 'ilobjectmetadatagui',
      2 => 'ilcertificategui',
      3 => 'ilpermissiongui',
      4 => 'iltestplayerfixedquestionsetgui',
      5 => 'iltestplayerrandomquestionsetgui',
      6 => 'iltestexpresspageobjectgui',
      7 => 'ilassquestionpagegui',
      8 => 'iltestdashboardgui',
      9 => 'iltestresultsgui',
      10 => 'illearningprogressgui',
      11 => 'ilmarkschemagui',
      12 => 'iltestevaluationgui',
      13 => 'ilparticipantstestresultsgui',
      14 => 'ilassgenfeedbackpagegui',
      15 => 'ilassspecfeedbackpagegui',
      16 => 'ilinfoscreengui',
      17 => 'ilobjectcopygui',
      18 => 'iltestscoringgui',
      19 => 'iltestscreengui',
      20 => 'ilrepositorysearchgui',
      21 => 'iltestexportgui',
      22 => 'assmultiplechoicegui',
      23 => 'assclozetestgui',
      24 => 'assmatchingquestiongui',
      25 => 'assorderingquestiongui',
      26 => 'assimagemapquestiongui',
      27 => 'assnumericgui',
      28 => 'asserrortextgui',
      29 => 'iltestscoringbyquestionsgui',
      30 => 'asstextsubsetgui',
      31 => 'assorderinghorizontalgui',
      32 => 'asssinglechoicegui',
      33 => 'assfileuploadgui',
      34 => 'asstextquestiongui',
      35 => 'asskprimchoicegui',
      36 => 'asslongmenugui',
      37 => 'ilobjquestionpoolgui',
      38 => 'ileditclipboardgui',
      39 => 'ilobjtestsettingsmaingui',
      40 => 'ilobjtestsettingsscoringresultsgui',
      41 => 'ilcommonactiondispatchergui',
      42 => 'iltestfixedquestionsetconfiggui',
      43 => 'iltestrandomquestionsetconfiggui',
      44 => 'ilassquestionhintsgui',
      45 => 'ilassquestionfeedbackeditinggui',
      46 => 'illocalunitconfigurationgui',
      47 => 'assformulaquestiongui',
      48 => 'iltestpassdetailsoverviewtablegui',
      49 => 'iltestresultstoolbargui',
      50 => 'iltestcorrectionsgui',
      51 => 'iltestsettingschangeconfirmationgui',
      52 => 'iltestskilladministrationgui',
      53 => 'ilassquestionpreviewgui',
      54 => 'iltestquestionbrowsertablegui',
      55 => 'iltestinfoscreentoolbargui',
      56 => 'illtiproviderobjectsettinggui',
      57 => 'iltestpagegui',
      58 => 'iltestservicegui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
      1 => 'ilobjlearningsequencegui',
      2 => 'ilobjquestionpoolgui',
      3 => 'ilrepositorygui',
    ),
  ),
  'ilobjtestsettingsmaingui' => 
  array (
    'cid' => 'qy',
    'class_name' => 'ilObjTestSettingsMainGUI',
    'class_path' => './components/ILIAS/Test/classes/MainSettings/class.ilObjTestSettingsMainGUI.php',
    'children' => 
    array (
      0 => 'ilpropertyformgui',
      1 => 'ilconfirmationgui',
      2 => 'iltestsettingschangeconfirmationgui',
    ),
    'parents' => 
    array (
      0 => 'ilobjtestgui',
    ),
  ),
  'ilobjtestsettingsscoringresultsgui' => 
  array (
    'cid' => 'qz',
    'class_name' => 'ilObjTestSettingsScoringResultsGUI',
    'class_path' => './components/ILIAS/Test/classes/class.ilObjTestSettingsScoringResultsGUI.php',
    'children' => 
    array (
      0 => 'ilpropertyformgui',
      1 => 'ilconfirmationgui',
    ),
    'parents' => 
    array (
      0 => 'ilobjtestgui',
    ),
  ),
  'ilobjtestverificationgui' => 
  array (
    'cid' => 'r0',
    'class_name' => 'ilObjTestVerificationGUI',
    'class_path' => './components/ILIAS/Test/classes/class.ilObjTestVerificationGUI.php',
    'children' => 
    array (
      0 => 'ilworkspaceaccessgui',
    ),
    'parents' => 
    array (
      0 => 'ilpersonalworkspacegui',
      1 => 'ilsharedresourcegui',
    ),
  ),
  'ilobjuserfoldergui' => 
  array (
    'cid' => 'r3',
    'class_name' => 'ilObjUserFolderGUI',
    'class_path' => './Services/User/classes/class.ilObjUserFolderGUI.php',
    'children' => 
    array (
      0 => 'ilpermissiongui',
      1 => 'ilusertablegui',
      2 => 'ilcustomuserfieldsgui',
      3 => 'ilrepositorysearchgui',
      4 => 'iluserstartingpointgui',
      5 => 'iluserprofileinfosettingsgui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
      1 => 'ilobjcategorygui',
      2 => 'ilobjorgunitgui',
    ),
  ),
  'ilobjusergui' => 
  array (
    'cid' => 'r4',
    'class_name' => 'ilObjUserGUI',
    'class_path' => './Services/User/classes/class.ilObjUserGUI.php',
    'children' => 
    array (
      0 => 'illearningprogressgui',
      1 => 'ilobjectownershipmanagementgui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
      1 => 'ildashboardgui',
      2 => 'ilmailgui',
      3 => 'ilobjbibliographicgui',
      4 => 'ilobjcategorygui',
      5 => 'ilobjdatacollectiongui',
      6 => 'ilobjorgunitgui',
    ),
  ),
  'ilobjusertrackinggui' => 
  array (
    'cid' => 'r5',
    'class_name' => 'ilObjUserTrackingGUI',
    'class_path' => './Services/Tracking/classes/class.ilObjUserTrackingGUI.php',
    'children' => 
    array (
      0 => 'illearningprogressgui',
      1 => 'ilpermissiongui',
      2 => 'illpobjectstatisticsgui',
      3 => 'ilsessionstatisticsgui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
    ),
  ),
  'ilobjwebdavgui' => 
  array (
    'cid' => 'r6',
    'class_name' => 'ilObjWebDAVGUI',
    'class_path' => './Services/WebDAV/classes/class.ilObjWebDAVGUI.php',
    'children' => 
    array (
      0 => 'ilpermissiongui',
      1 => 'ilwebdavmountinstructionsuploadgui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
    ),
  ),
  'ilobjwebresourceadministrationgui' => 
  array (
    'cid' => 'r7',
    'class_name' => 'ilObjWebResourceAdministrationGUI',
    'class_path' => './components/ILIAS/WebResource/classes/class.ilObjWebResourceAdministrationGUI.php',
    'children' => 
    array (
      0 => 'ilpermissiongui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
    ),
  ),
  'ilobjwikigui' => 
  array (
    'cid' => 'r8',
    'class_name' => 'ilObjWikiGUI',
    'class_path' => './components/ILIAS/Wiki/classes/class.ilObjWikiGUI.php',
    'children' => 
    array (
      0 => 'ilpermissiongui',
      1 => 'ilinfoscreengui',
      2 => 'ilwikipagegui',
      3 => 'ilpublicuserprofilegui',
      4 => 'ilobjectcontentstylesettingsgui',
      5 => 'ilexportgui',
      6 => 'ilcommonactiondispatchergui',
      7 => 'ilratinggui',
      8 => 'ilwikipagetemplategui',
      9 => 'ilwikistatgui',
      10 => 'ilobjectmetadatagui',
      11 => 'ilsettingspermissiongui',
      12 => 'ilrepositoryobjectsearchgui',
      13 => 'ilobjectcopygui',
      14 => 'ilobjnotificationsettingsgui',
      15 => 'illtiproviderobjectsettinggui',
      16 => 'ilobjecttranslationgui',
    ),
    'parents' => 
    array (
      0 => 'ilrepositorygui',
      1 => 'iladministrationgui',
      2 => 'ilwikihandlergui',
    ),
  ),
  'ilobjwikisettingsgui' => 
  array (
    'cid' => 'rb',
    'class_name' => 'ilObjWikiSettingsGUI',
    'class_path' => './components/ILIAS/Wiki/classes/class.ilObjWikiSettingsGUI.php',
    'children' => 
    array (
      0 => 'ilpermissiongui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
    ),
  ),
  'ilobjworkspacefoldergui' => 
  array (
    'cid' => 'rd',
    'class_name' => 'ilObjWorkspaceFolderGUI',
    'class_path' => './components/ILIAS/WorkspaceFolder/classes/class.ilObjWorkspaceFolderGUI.php',
    'children' => 
    array (
      0 => 'ilcommonactiondispatchergui',
      1 => 'ilobjectownershipmanagementgui',
    ),
    'parents' => 
    array (
      0 => 'ilpersonalworkspacegui',
    ),
  ),
  'ilobjworkspacerootfoldergui' => 
  array (
    'cid' => 'rg',
    'class_name' => 'ilObjWorkspaceRootFolderGUI',
    'class_path' => './components/ILIAS/WorkspaceRootFolder/classes/class.ilObjWorkspaceRootFolderGUI.php',
    'children' => 
    array (
      0 => 'ilcommonactiondispatchergui',
      1 => 'ilobjectownershipmanagementgui',
    ),
    'parents' => 
    array (
      0 => 'ilpersonalworkspacegui',
    ),
  ),
  'ilobjectactivationgui' => 
  array (
    'cid' => 'rj',
    'class_name' => 'ilObjectActivationGUI',
    'class_path' => './Services/Object/classes/class.ilObjectActivationGUI.php',
    'children' => 
    array (
      0 => 'ilconditionhandlergui',
    ),
    'parents' => 
    array (
      0 => 'ilcommonactiondispatchergui',
    ),
  ),
  'ilobjectcontentstylesettingsgui' => 
  array (
    'cid' => 'rn',
    'class_name' => 'ilObjectContentStyleSettingsGUI',
    'class_path' => './Services/Style/Content/Object/class.ilObjectContentStyleSettingsGUI.php',
    'children' => 
    array (
      0 => 'ilobjstylesheetgui',
    ),
    'parents' => 
    array (
      0 => 'ilforumsettingsgui',
      1 => 'ilobjbloggui',
      2 => 'ilobjcategorygui',
      3 => 'ilobjcontentobjectgui',
      4 => 'ilobjcontentpagegui',
      5 => 'ilobjcoursegui',
      6 => 'ilobjfoldergui',
      7 => 'ilobjforumgui',
      8 => 'ilobjglossarygui',
      9 => 'ilobjgroupgui',
      10 => 'ilobjlearningmodulegui',
      11 => 'ilobjportfoliogui',
      12 => 'ilobjportfoliotemplategui',
      13 => 'ilobjrootfoldergui',
      14 => 'ilobjstudyprogrammegui',
      15 => 'ilobjwikigui',
    ),
  ),
  'ilobjectcopygui' => 
  array (
    'cid' => 'rp',
    'class_name' => 'ilObjectCopyGUI',
    'class_path' => './Services/Object/classes/class.ilObjectCopyGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'iladvancedsearchgui',
      1 => 'ilcoursecontentgui',
      2 => 'illuceneadvancedsearchgui',
      3 => 'illucenesearchgui',
      4 => 'ilobjbibliographicgui',
      5 => 'ilobjbloggui',
      6 => 'ilobjbookingpoolgui',
      7 => 'ilobjcategorygui',
      8 => 'ilobjchatroomadmingui',
      9 => 'ilobjchatroomgui',
      10 => 'ilobjcloudgui',
      11 => 'ilobjcmixapigui',
      12 => 'ilobjcontentobjectgui',
      13 => 'ilobjcontentpagegui',
      14 => 'ilobjcoursegui',
      15 => 'ilobjdatacollectiongui',
      16 => 'ilobjemployeetalkgui',
      17 => 'ilobjemployeetalkseriesgui',
      18 => 'ilobjexercisegui',
      19 => 'ilobjfilegui',
      20 => 'ilobjfoldergui',
      21 => 'ilobjforumgui',
      22 => 'ilobjglossarygui',
      23 => 'ilobjgroupgui',
      24 => 'ilobjindividualassessmentgui',
      25 => 'ilobjitemgroupgui',
      26 => 'ilobjlticonsumergui',
      27 => 'ilobjlearningmodulegui',
      28 => 'ilobjlearningsequencegui',
      29 => 'ilobjlinkresourcegui',
      30 => 'ilobjmediacastgui',
      31 => 'ilobjmediapoolgui',
      32 => 'ilobjorgunitgui',
      33 => 'ilobjpollgui',
      34 => 'ilobjportfoliotemplategui',
      35 => 'ilobjquestionpoolgui',
      36 => 'ilobjrootfoldergui',
      37 => 'ilobjsahslearningmodulegui',
      38 => 'ilobjsessiongui',
      39 => 'ilobjstudyprogrammegui',
      40 => 'ilobjsurveygui',
      41 => 'ilobjsurveyquestionpoolgui',
      42 => 'ilobjtalktemplateadministrationgui',
      43 => 'ilobjtalktemplategui',
      44 => 'ilobjtestgui',
      45 => 'ilobjwikigui',
      46 => 'ilpersonalworkspacegui',
      47 => 'ilsearchgui',
    ),
  ),
  'ilobjectcustomiconuploadhandlergui' => 
  array (
    'cid' => 'ru',
    'class_name' => 'ilObjectCustomIconUploadHandlerGUI',
    'class_path' => './Services/Object/classes/Properties/AdditionalProperties/Icon/class.ilObjectCustomIconUploadHandlerGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
    ),
  ),
  'ilobjectcustomuserfieldsgui' => 
  array (
    'cid' => 'rv',
    'class_name' => 'ilObjectCustomUserFieldsGUI',
    'class_path' => './Services/Membership/classes/class.ilObjectCustomUserFieldsGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilcoursemembershipgui',
      1 => 'ilgroupmembershipgui',
      2 => 'illearningsequencemembershipgui',
      3 => 'ilobjcoursegui',
      4 => 'ilobjgroupgui',
    ),
  ),
  'ilobjectgui' => 
  array (
    'cid' => 'rx',
    'class_name' => 'ilObjectGUI',
    'class_path' => './Services/Object/classes/class.ilObjectGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'iladvancedsearchgui',
      1 => 'illuceneadvancedsearchgui',
      2 => 'illucenesearchgui',
      3 => 'ilsearchgui',
    ),
  ),
  'ilobjectmetadatablockgui' => 
  array (
    'cid' => 'rz',
    'class_name' => 'ilObjectMetaDataBlockGUI',
    'class_path' => './Services/Object/classes/class.ilObjectMetaDataBlockGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilcolumngui',
    ),
  ),
  'ilobjectmetadatagui' => 
  array (
    'cid' => 's0',
    'class_name' => 'ilObjectMetaDataGUI',
    'class_path' => './Services/Object/classes/class.ilObjectMetaDataGUI.php',
    'children' => 
    array (
      0 => 'ilmdeditorgui',
      1 => 'iladvancedmdsettingsgui',
      2 => 'ilpropertyformgui',
      3 => 'iltaxmdgui',
      4 => 'ilobjtaxonomygui',
    ),
    'parents' => 
    array (
      0 => 'ilglossarydefpagegui',
      1 => 'ilglossarytermgui',
      2 => 'illmpagegui',
      3 => 'ilmediapoolpagegui',
      4 => 'ilobjbookingpoolgui',
      5 => 'ilobjcategorygui',
      6 => 'ilobjcmixapigui',
      7 => 'ilobjcontentobjectgui',
      8 => 'ilobjcoursegui',
      9 => 'ilobjexercisegui',
      10 => 'ilobjfilebasedlmgui',
      11 => 'ilobjfilegui',
      12 => 'ilobjglossarygui',
      13 => 'ilobjgroupgui',
      14 => 'ilobjindividualassessmentgui',
      15 => 'ilobjlticonsumergui',
      16 => 'ilobjlearningmodulegui',
      17 => 'ilobjlinkresourcegui',
      18 => 'ilobjmediaobjectgui',
      19 => 'ilobjmediapoolgui',
      20 => 'ilobjportfoliotemplategui',
      21 => 'ilobjquestionpoolgui',
      22 => 'ilobjsahslearningmodulegui',
      23 => 'ilobjscorm2004learningmodulegui',
      24 => 'ilobjscormlearningmodulegui',
      25 => 'ilobjsessiongui',
      26 => 'ilobjsurveygui',
      27 => 'ilobjsurveyquestionpoolgui',
      28 => 'ilobjtestgui',
      29 => 'ilobjwikigui',
      30 => 'ilpageobjectgui',
      31 => 'ilsahseditgui',
      32 => 'ilstructureobjectgui',
      33 => 'ilwikipagegui',
    ),
  ),
  'ilobjectownershipmanagementgui' => 
  array (
    'cid' => 's1',
    'class_name' => 'ilObjectOwnershipManagementGUI',
    'class_path' => './Services/Object/classes/class.ilObjectOwnershipManagementGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjsystemcheckgui',
      1 => 'ilobjsystemfoldergui',
      2 => 'ilobjusergui',
      3 => 'ilobjworkspacefoldergui',
      4 => 'ilobjworkspacerootfoldergui',
    ),
  ),
  'ilobjectpermissionstatusgui' => 
  array (
    'cid' => 's3',
    'class_name' => 'ilObjectPermissionStatusGUI',
    'class_path' => './Services/AccessControl/classes/class.ilObjectPermissionStatusGUI.php',
    'children' => 
    array (
      0 => 'ilrepositorysearchgui',
    ),
    'parents' => 
    array (
      0 => 'ilpermissiongui',
    ),
  ),
  'ilobjectservicesettingsgui' => 
  array (
    'cid' => 's9',
    'class_name' => 'ilObjectServiceSettingsGUI',
    'class_path' => './Services/Object/classes/class.ilObjectServiceSettingsGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjcoursegui',
      1 => 'ilobjgroupgui',
    ),
  ),
  'ilobjecttileimageuploadhandlergui' => 
  array (
    'cid' => 'sc',
    'class_name' => 'ilObjectTileImageUploadHandlerGUI',
    'class_path' => './Services/Object/classes/Properties/CoreProperties/TileImage/class.ilObjectTileImageUploadHandlerGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
    ),
  ),
  'ilobjecttranslationgui' => 
  array (
    'cid' => 'se',
    'class_name' => 'ilObjectTranslationGUI',
    'class_path' => './Services/Object/classes/Translation/class.ilObjectTranslationGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjcategorygui',
      1 => 'ilobjcontentobjectgui',
      2 => 'ilobjcontentpagegui',
      3 => 'ilobjcoursegui',
      4 => 'ilobjfoldergui',
      5 => 'ilobjgroupgui',
      6 => 'ilobjitemgroupgui',
      7 => 'ilobjlearningmodulegui',
      8 => 'ilobjmediapoolgui',
      9 => 'ilobjrootfoldergui',
      10 => 'ilobjstudyprogrammegui',
      11 => 'ilobjwikigui',
    ),
  ),
  'ilonscreenchatgui' => 
  array (
    'cid' => 'sg',
    'class_name' => 'ilOnScreenChatGUI',
    'class_path' => './Services/OnScreenChat/classes/class.ilOnScreenChatGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
    ),
  ),
  'ilopenidconnectsettingsgui' => 
  array (
    'cid' => 'sh',
    'class_name' => 'ilOpenIdConnectSettingsGUI',
    'class_path' => './Services/OpenIdConnect/classes/class.ilOpenIdConnectSettingsGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjauthsettingsgui',
    ),
  ),
  'ilorgunitdefaultpermissiongui' => 
  array (
    'cid' => 'sm',
    'class_name' => 'ilOrgUnitDefaultPermissionGUI',
    'class_path' => './components/ILIAS/OrgUnit/classes/Positions/Permissions/class.ilOrgUnitDefaultPermissionGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilorgunitpositiongui',
    ),
  ),
  'ilorgunitexportgui' => 
  array (
    'cid' => 'sp',
    'class_name' => 'ilOrgUnitExportGUI',
    'class_path' => './components/ILIAS/OrgUnit/classes/class.ilOrgUnitExportGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjorgunitgui',
    ),
  ),
  'ilorgunitglobalsettingsgui' => 
  array (
    'cid' => 'st',
    'class_name' => 'ilOrgUnitGlobalSettingsGUI',
    'class_path' => './components/ILIAS/OrgUnit/classes/Settings/class.ilOrgUnitGlobalSettingsGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjorgunitgui',
    ),
  ),
  'ilorgunitpermissiongui' => 
  array (
    'cid' => 'sv',
    'class_name' => 'ilOrgUnitPermissionGUI',
    'class_path' => './components/ILIAS/OrgUnit/classes/Positions/Permissions/class.ilOrgUnitPermissionGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilorgunitpositiongui',
    ),
  ),
  'ilorgunitpositiongui' => 
  array (
    'cid' => 'sy',
    'class_name' => 'ilOrgUnitPositionGUI',
    'class_path' => './components/ILIAS/OrgUnit/classes/Positions/class.ilOrgUnitPositionGUI.php',
    'children' => 
    array (
      0 => 'ilorgunitdefaultpermissiongui',
      1 => 'ilorgunitpermissiongui',
    ),
    'parents' => 
    array (
      0 => 'ilobjorgunitgui',
    ),
  ),
  'ilorgunitsimpleimportgui' => 
  array (
    'cid' => 't1',
    'class_name' => 'ilOrgUnitSimpleImportGUI',
    'class_path' => './components/ILIAS/OrgUnit/classes/SimpleImport/class.ilOrgUnitSimpleImportGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjorgunitgui',
    ),
  ),
  'ilorgunitsimpleuserimportgui' => 
  array (
    'cid' => 't2',
    'class_name' => 'ilOrgUnitSimpleUserImportGUI',
    'class_path' => './components/ILIAS/OrgUnit/classes/SimpleUserImport/class.ilOrgUnitSimpleUserImportGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjorgunitgui',
    ),
  ),
  'ilorgunittypegui' => 
  array (
    'cid' => 't6',
    'class_name' => 'ilOrgUnitTypeGUI',
    'class_path' => './components/ILIAS/OrgUnit/classes/Types/class.ilOrgUnitTypeGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjorgunitgui',
    ),
  ),
  'ilorgunituserassignmentgui' => 
  array (
    'cid' => 't8',
    'class_name' => 'ilOrgUnitUserAssignmentGUI',
    'class_path' => './components/ILIAS/OrgUnit/classes/Positions/UserAssignment/class.ilOrgUnitUserAssignmentGUI.php',
    'children' => 
    array (
      0 => 'ilrepositorysearchgui',
    ),
    'parents' => 
    array (
      0 => 'ilobjorgunitgui',
    ),
  ),
  'ilpcamdformgui' => 
  array (
    'cid' => 'tb',
    'class_name' => 'ilPCAMDFormGUI',
    'class_path' => './components/ILIAS/Portfolio/Page/class.ilPCAMDFormGUI.php',
    'children' => 
    array (
      0 => 'ilpropertyformgui',
    ),
    'parents' => 
    array (
      0 => 'ilpageeditorgui',
    ),
  ),
  'ilpcamdpagelistgui' => 
  array (
    'cid' => 'tc',
    'class_name' => 'ilPCAMDPageListGUI',
    'class_path' => './components/ILIAS/Wiki/classes/class.ilPCAMDPageListGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilpageeditorgui',
    ),
  ),
  'ilpcbloggui' => 
  array (
    'cid' => 'td',
    'class_name' => 'ilPCBlogGUI',
    'class_path' => './Services/COPage/PC/Blog/class.ilPCBlogGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilpageeditorgui',
    ),
  ),
  'ilpcconsultationhoursgui' => 
  array (
    'cid' => 'te',
    'class_name' => 'ilPCConsultationHoursGUI',
    'class_path' => './components/ILIAS/Portfolio/Page/class.ilPCConsultationHoursGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilpageeditorgui',
    ),
  ),
  'ilpccontentincludegui' => 
  array (
    'cid' => 'tf',
    'class_name' => 'ilPCContentIncludeGUI',
    'class_path' => './Services/COPage/PC/ContentInclude/class.ilPCContentIncludeGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilpageeditorgui',
    ),
  ),
  'ilpccontenttemplategui' => 
  array (
    'cid' => 'tg',
    'class_name' => 'ilPCContentTemplateGUI',
    'class_path' => './Services/COPage/PC/ContentTemplate/class.ilPCContentTemplateGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilpageeditorgui',
    ),
  ),
  'ilpccurriculumgui' => 
  array (
    'cid' => 'th',
    'class_name' => 'ilPCCurriculumGUI',
    'class_path' => './components/ILIAS/LearningSequence/classes/PageEditor/class.ilPCCurriculumGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilpageeditorgui',
    ),
  ),
  'ilpcdatatablegui' => 
  array (
    'cid' => 'tj',
    'class_name' => 'ilPCDataTableGUI',
    'class_path' => './Services/COPage/PC/Table/class.ilPCDataTableGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilpageeditorgui',
    ),
  ),
  'ilpcfileitemgui' => 
  array (
    'cid' => 'tl',
    'class_name' => 'ilPCFileItemGUI',
    'class_path' => './Services/COPage/PC/FileList/class.ilPCFileItemGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilpageeditorgui',
    ),
  ),
  'ilpcfilelistgui' => 
  array (
    'cid' => 'tm',
    'class_name' => 'ilPCFileListGUI',
    'class_path' => './Services/COPage/PC/FileList/class.ilPCFileListGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilpageeditorgui',
    ),
  ),
  'ilpcgridcellgui' => 
  array (
    'cid' => 'to',
    'class_name' => 'ilPCGridCellGUI',
    'class_path' => './Services/COPage/PC/Grid/class.ilPCGridCellGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilpageeditorgui',
    ),
  ),
  'ilpcgridgui' => 
  array (
    'cid' => 'tr',
    'class_name' => 'ilPCGridGUI',
    'class_path' => './Services/COPage/PC/Grid/class.ilPCGridGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilpageeditorgui',
    ),
  ),
  'ilpciimtriggereditorgui' => 
  array (
    'cid' => 'tu',
    'class_name' => 'ilPCIIMTriggerEditorGUI',
    'class_path' => './Services/COPage/PC/InteractiveImage/class.ilPCIIMTriggerEditorGUI.php',
    'children' => 
    array (
      0 => 'ilinternallinkgui',
    ),
    'parents' => 
    array (
      0 => 'ilpcinteractiveimagegui',
    ),
  ),
  'ilpcimagemapeditorgui' => 
  array (
    'cid' => 'tw',
    'class_name' => 'ilPCImageMapEditorGUI',
    'class_path' => './Services/COPage/PC/MediaObject/class.ilPCImageMapEditorGUI.php',
    'children' => 
    array (
      0 => 'ilinternallinkgui',
    ),
    'parents' => 
    array (
      0 => 'ilpcmediaobjectgui',
    ),
  ),
  'ilpcinteractiveimagegui' => 
  array (
    'cid' => 'ty',
    'class_name' => 'ilPCInteractiveImageGUI',
    'class_path' => './Services/COPage/PC/InteractiveImage/class.ilPCInteractiveImageGUI.php',
    'children' => 
    array (
      0 => 'ilpciimtriggereditorgui',
      1 => 'ilrepostandarduploadhandlergui',
    ),
    'parents' => 
    array (
      0 => 'ilpageeditorgui',
    ),
  ),
  'ilpclaunchergui' => 
  array (
    'cid' => 'tz',
    'class_name' => 'ilPCLauncherGUI',
    'class_path' => './components/ILIAS/LearningSequence/classes/PageEditor/class.ilPCLauncherGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilpageeditorgui',
    ),
  ),
  'ilpclayouttemplategui' => 
  array (
    'cid' => 'u1',
    'class_name' => 'ilPCLayoutTemplateGUI',
    'class_path' => './Services/COPage/PC/LayoutTemplate/class.ilPCLayoutTemplateGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilpageeditorgui',
    ),
  ),
  'ilpclearninghistorygui' => 
  array (
    'cid' => 'u2',
    'class_name' => 'ilPCLearningHistoryGUI',
    'class_path' => './Services/LearningHistory/classes/class.ilPCLearningHistoryGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilpageeditorgui',
    ),
  ),
  'ilpclistgui' => 
  array (
    'cid' => 'u3',
    'class_name' => 'ilPCListGUI',
    'class_path' => './Services/COPage/PC/List/class.ilPCListGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilpageeditorgui',
    ),
  ),
  'ilpclistitemgui' => 
  array (
    'cid' => 'u4',
    'class_name' => 'ilPCListItemGUI',
    'class_path' => './Services/COPage/PC/List/class.ilPCListItemGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilpageeditorgui',
    ),
  ),
  'ilpcloginpageelementgui' => 
  array (
    'cid' => 'u5',
    'class_name' => 'ilPCLoginPageElementGUI',
    'class_path' => './Services/COPage/PC/Login/class.ilPCLoginPageElementGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilpageeditorgui',
    ),
  ),
  'ilpcmapgui' => 
  array (
    'cid' => 'u6',
    'class_name' => 'ilPCMapGUI',
    'class_path' => './Services/COPage/PC/Map/class.ilPCMapGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilpageeditorgui',
    ),
  ),
  'ilpcmediaobjectgui' => 
  array (
    'cid' => 'u8',
    'class_name' => 'ilPCMediaObjectGUI',
    'class_path' => './Services/COPage/PC/MediaObject/class.ilPCMediaObjectGUI.php',
    'children' => 
    array (
      0 => 'ilobjmediaobjectgui',
      1 => 'ilpcimagemapeditorgui',
    ),
    'parents' => 
    array (
      0 => 'ilpcplaceholdergui',
      1 => 'ilpageeditorgui',
    ),
  ),
  'ilpcmycoursesgui' => 
  array (
    'cid' => 'u9',
    'class_name' => 'ilPCMyCoursesGUI',
    'class_path' => './components/ILIAS/Portfolio/Page/class.ilPCMyCoursesGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilpageeditorgui',
    ),
  ),
  'ilpcprgactionnotegui' => 
  array (
    'cid' => 'ua',
    'class_name' => 'ilPCPRGActionNoteGUI',
    'class_path' => './components/ILIAS/StudyProgramme/classes/PageEditor/class.ilPCPRGActionNoteGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilpageeditorgui',
    ),
  ),
  'ilpcprgstatusinfogui' => 
  array (
    'cid' => 'ub',
    'class_name' => 'ilPCPRGStatusInfoGUI',
    'class_path' => './components/ILIAS/StudyProgramme/classes/PageEditor/class.ilPCPRGStatusInfoGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilpageeditorgui',
    ),
  ),
  'ilpcparagraphgui' => 
  array (
    'cid' => 'ud',
    'class_name' => 'ilPCParagraphGUI',
    'class_path' => './Services/COPage/PC/Paragraph/class.ilPCParagraphGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilpageeditorgui',
    ),
  ),
  'ilpcplaceholdergui' => 
  array (
    'cid' => 'uf',
    'class_name' => 'ilPCPlaceHolderGUI',
    'class_path' => './Services/COPage/PC/PlaceHolder/class.ilPCPlaceHolderGUI.php',
    'children' => 
    array (
      0 => 'ilpcmediaobjectgui',
    ),
    'parents' => 
    array (
      0 => 'ilpageeditorgui',
    ),
  ),
  'ilpcpluggedgui' => 
  array (
    'cid' => 'ug',
    'class_name' => 'ilPCPluggedGUI',
    'class_path' => './Services/COPage/PC/Plugged/class.ilPCPluggedGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilpageeditorgui',
    ),
  ),
  'ilpcprofilegui' => 
  array (
    'cid' => 'uh',
    'class_name' => 'ilPCProfileGUI',
    'class_path' => './Services/COPage/PC/Profile/class.ilPCProfileGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilpageeditorgui',
    ),
  ),
  'ilpcquestiongui' => 
  array (
    'cid' => 'ui',
    'class_name' => 'ilPCQuestionGUI',
    'class_path' => './Services/COPage/PC/Question/class.ilPCQuestionGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilpageeditorgui',
    ),
  ),
  'ilpcresourcesgui' => 
  array (
    'cid' => 'uk',
    'class_name' => 'ilPCResourcesGUI',
    'class_path' => './Services/COPage/PC/Resources/class.ilPCResourcesGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilpageeditorgui',
    ),
  ),
  'ilpcsectiongui' => 
  array (
    'cid' => 'um',
    'class_name' => 'ilPCSectionGUI',
    'class_path' => './Services/COPage/PC/Section/class.ilPCSectionGUI.php',
    'children' => 
    array (
      0 => 'ilpropertyformgui',
    ),
    'parents' => 
    array (
      0 => 'ilpageeditorgui',
    ),
  ),
  'ilpcskillsgui' => 
  array (
    'cid' => 'un',
    'class_name' => 'ilPCSkillsGUI',
    'class_path' => './Services/COPage/PC/Skills/class.ilPCSkillsGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilpageeditorgui',
    ),
  ),
  'ilpcsourcecodegui' => 
  array (
    'cid' => 'up',
    'class_name' => 'ilPCSourceCodeGUI',
    'class_path' => './Services/COPage/PC/SourceCode/class.ilPCSourceCodeGUI.php',
    'children' => 
    array (
      0 => 'ilrepostandarduploadhandlergui',
    ),
    'parents' => 
    array (
      0 => 'ilpageeditorgui',
    ),
  ),
  'ilpctabgui' => 
  array (
    'cid' => 'uq',
    'class_name' => 'ilPCTabGUI',
    'class_path' => './Services/COPage/PC/Tabs/class.ilPCTabGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilpageeditorgui',
    ),
  ),
  'ilpctabledatagui' => 
  array (
    'cid' => 'ur',
    'class_name' => 'ilPCTableDataGUI',
    'class_path' => './Services/COPage/PC/Table/class.ilPCTableDataGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilpageeditorgui',
    ),
  ),
  'ilpctablegui' => 
  array (
    'cid' => 'us',
    'class_name' => 'ilPCTableGUI',
    'class_path' => './Services/COPage/PC/Table/class.ilPCTableGUI.php',
    'children' => 
    array (
      0 => 'ilassgenfeedbackpagegui',
    ),
    'parents' => 
    array (
      0 => 'ilpageeditorgui',
    ),
  ),
  'ilpctabsgui' => 
  array (
    'cid' => 'uu',
    'class_name' => 'ilPCTabsGUI',
    'class_path' => './Services/COPage/PC/Tabs/class.ilPCTabsGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilpageeditorgui',
    ),
  ),
  'ilpcverificationgui' => 
  array (
    'cid' => 'uw',
    'class_name' => 'ilPCVerificationGUI',
    'class_path' => './Services/COPage/PC/Verification/class.ilPCVerificationGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilpageeditorgui',
    ),
  ),
  'ilpdcalendarblockgui' => 
  array (
    'cid' => 'ux',
    'class_name' => 'ilPDCalendarBlockGUI',
    'class_path' => './Services/Calendar/classes/class.ilPDCalendarBlockGUI.php',
    'children' => 
    array (
      0 => 'ilcalendardaygui',
      1 => 'ilcalendarappointmentgui',
      2 => 'ilcalendarmonthgui',
      3 => 'ilcalendarweekgui',
      4 => 'ilcalendarinboxgui',
      5 => 'ilconsultationhoursgui',
      6 => 'ilcalendarappointmentpresentationgui',
    ),
    'parents' => 
    array (
      0 => 'ilcolumngui',
      1 => 'ilcalendarpresentationgui',
    ),
  ),
  'ilpdmailblockgui' => 
  array (
    'cid' => 'uy',
    'class_name' => 'ilPDMailBlockGUI',
    'class_path' => './Services/Mail/classes/class.ilPDMailBlockGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilcolumngui',
      1 => 'ildashboardgui',
    ),
  ),
  'ilpdnewsblockgui' => 
  array (
    'cid' => 'v0',
    'class_name' => 'ilPDNewsBlockGUI',
    'class_path' => './Services/News/classes/class.ilPDNewsBlockGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilcolumngui',
    ),
  ),
  'ilpdnewsgui' => 
  array (
    'cid' => 'v1',
    'class_name' => 'ilPDNewsGUI',
    'class_path' => './Services/News/classes/class.ilPDNewsGUI.php',
    'children' => 
    array (
      0 => 'ilnewstimelinegui',
      1 => 'ilcommonactiondispatchergui',
    ),
    'parents' => 
    array (
      0 => 'ildashboardgui',
    ),
  ),
  'ilpdnotesgui' => 
  array (
    'cid' => 'v3',
    'class_name' => 'ilPDNotesGUI',
    'class_path' => './Services/Notes/Note/class.ilPDNotesGUI.php',
    'children' => 
    array (
      0 => 'ilnotegui',
    ),
    'parents' => 
    array (
      0 => 'ildashboardgui',
    ),
  ),
  'ilpdstudyprogrammeexpandablelistgui' => 
  array (
    'cid' => 'v4',
    'class_name' => 'ilPDStudyProgrammeExpandableListGUI',
    'class_path' => './components/ILIAS/StudyProgramme/classes/class.ilPDStudyProgrammeExpandableListGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilcolumngui',
    ),
  ),
  'ilpdstudyprogrammesimplelistgui' => 
  array (
    'cid' => 'v5',
    'class_name' => 'ilPDStudyProgrammeSimpleListGUI',
    'class_path' => './components/ILIAS/StudyProgramme/classes/class.ilPDStudyProgrammeSimpleListGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilcolumngui',
    ),
  ),
  'ilpdtasksblockgui' => 
  array (
    'cid' => 'v6',
    'class_name' => 'ilPDTasksBlockGUI',
    'class_path' => './Services/Tasks/classes/class.ilPDTasksBlockGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilcolumngui',
    ),
  ),
  'ilprgmembersexportgui' => 
  array (
    'cid' => 'v7',
    'class_name' => 'ilPRGMembersExportGUI',
    'class_path' => './components/ILIAS/StudyProgramme/classes/memberexport/class.ilPRGMembersExportGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjstudyprogrammegui',
    ),
  ),
  'ilprgpageobjectgui' => 
  array (
    'cid' => 'v8',
    'class_name' => 'ilPRGPageObjectGUI',
    'class_path' => './components/ILIAS/StudyProgramme/classes/PageEditor/class.ilPRGPageObjectGUI.php',
    'children' => 
    array (
      0 => 'ilpageeditorgui',
      1 => 'ileditclipboardgui',
    ),
    'parents' => 
    array (
      0 => 'ilobjstudyprogrammegui',
    ),
  ),
  'ilpageeditorgui' => 
  array (
    'cid' => 'vb',
    'class_name' => 'ilPageEditorGUI',
    'class_path' => './Services/COPage/classes/class.ilPageEditorGUI.php',
    'children' => 
    array (
      0 => 'ilpcparagraphgui',
      1 => 'ilpctablegui',
      2 => 'ilpctabledatagui',
      3 => 'ilpcmediaobjectgui',
      4 => 'ilpclistgui',
      5 => 'ilpclistitemgui',
      6 => 'ilpcfilelistgui',
      7 => 'ilpcfileitemgui',
      8 => 'ilobjmediaobjectgui',
      9 => 'ilpcsourcecodegui',
      10 => 'ilinternallinkgui',
      11 => 'ilpcquestiongui',
      12 => 'ilpcsectiongui',
      13 => 'ilpcdatatablegui',
      14 => 'ilpcresourcesgui',
      15 => 'ilpcmapgui',
      16 => 'ilpcpluggedgui',
      17 => 'ilpctabsgui',
      18 => 'ilpctabgui',
      19 => 'ilpcplaceholdergui',
      20 => 'ilpccontentincludegui',
      21 => 'ilpcloginpageelementgui',
      22 => 'ilpcinteractiveimagegui',
      23 => 'ilpcprofilegui',
      24 => 'ilpcverificationgui',
      25 => 'ilpcbloggui',
      26 => 'ilpcskillsgui',
      27 => 'ilpcconsultationhoursgui',
      28 => 'ilpcmycoursesgui',
      29 => 'ilpcamdpagelistgui',
      30 => 'ilpcgridgui',
      31 => 'ilpcgridcellgui',
      32 => 'ilpageeditorserveradaptergui',
      33 => 'ilpcamdformgui',
      34 => 'ilpccontenttemplategui',
      35 => 'ilpccurriculumgui',
      36 => 'ilpclaunchergui',
      37 => 'ilpclayouttemplategui',
      38 => 'ilpclearninghistorygui',
      39 => 'ilpcprgactionnotegui',
      40 => 'ilpcprgstatusinfogui',
    ),
    'parents' => 
    array (
      0 => 'ilassgenfeedbackpagegui',
      1 => 'ilasshintpagegui',
      2 => 'ilassquestionpagegui',
      3 => 'ilassspecfeedbackpagegui',
      4 => 'ilblogpostinggui',
      5 => 'ilcontainerpagegui',
      6 => 'ilcontainerstartobjectspagegui',
      7 => 'ilcontentpagepagegui',
      8 => 'ildclcreateviewdefinitiongui',
      9 => 'ildcldetailedviewdefinitiongui',
      10 => 'ildcleditviewdefinitiongui',
      11 => 'ilforumpagegui',
      12 => 'ilglossarydefpagegui',
      13 => 'ilimprintgui',
      14 => 'illmpagegui',
      15 => 'illopagegui',
      16 => 'illoginpagegui',
      17 => 'ilmediapoolpagegui',
      18 => 'ilobjlearningsequenceeditextrogui',
      19 => 'ilobjlearningsequenceeditintrogui',
      20 => 'ilprgpageobjectgui',
      21 => 'ilpagelayoutgui',
      22 => 'ilpageobjectgui',
      23 => 'ilportfoliopagegui',
      24 => 'ilportfoliotemplatepagegui',
      25 => 'iltestexpresspageobjectgui',
      26 => 'iltestfixedquestionsetconfiggui',
      27 => 'iltestpagegui',
      28 => 'ilwikipagegui',
    ),
  ),
  'ilpageeditorserveradaptergui' => 
  array (
    'cid' => 'vc',
    'class_name' => 'ilPageEditorServerAdapterGUI',
    'class_path' => './Services/COPage/Editor/class.ilPageEditorServerAdapterGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilpageeditorgui',
    ),
  ),
  'ilpagelayoutadministrationgui' => 
  array (
    'cid' => 've',
    'class_name' => 'ilPageLayoutAdministrationGUI',
    'class_path' => './Services/COPage/Layout/Administration/class.ilPageLayoutAdministrationGUI.php',
    'children' => 
    array (
      0 => 'ilpagelayoutgui',
    ),
    'parents' => 
    array (
      0 => 'ilobjstylesettingsgui',
    ),
  ),
  'ilpagelayoutgui' => 
  array (
    'cid' => 'vf',
    'class_name' => 'ilPageLayoutGUI',
    'class_path' => './Services/COPage/Layout/classes/class.ilPageLayoutGUI.php',
    'children' => 
    array (
      0 => 'ilpageeditorgui',
      1 => 'ileditclipboardgui',
      2 => 'ilpublicuserprofilegui',
      3 => 'ilpageobjectgui',
    ),
    'parents' => 
    array (
      0 => 'ilpagelayoutadministrationgui',
    ),
  ),
  'ilpagemultilanggui' => 
  array (
    'cid' => 'vh',
    'class_name' => 'ilPageMultiLangGUI',
    'class_path' => './Services/COPage/classes/class.ilPageMultiLangGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilcontainerpagegui',
      1 => 'ilcontainerstartobjectspagegui',
      2 => 'ilcontentpagepagegui',
      3 => 'ilforumpagegui',
      4 => 'illmpagegui',
      5 => 'illopagegui',
      6 => 'ilobjcontentobjectgui',
      7 => 'ilobjcontentpagegui',
      8 => 'ilobjlearningmodulegui',
      9 => 'ilpageobjectgui',
    ),
  ),
  'ilpageobjectgui' => 
  array (
    'cid' => 'vj',
    'class_name' => 'ilPageObjectGUI',
    'class_path' => './Services/COPage/classes/class.ilPageObjectGUI.php',
    'children' => 
    array (
      0 => 'ilpageeditorgui',
      1 => 'ileditclipboardgui',
      2 => 'ilobjectmetadatagui',
      3 => 'ilpublicuserprofilegui',
      4 => 'ilnotegui',
      5 => 'ilcommentgui',
      6 => 'ilnewsitemgui',
      7 => 'ilpropertyformgui',
      8 => 'ilinternallinkgui',
      9 => 'ilpagemultilanggui',
      10 => 'illearninghistorygui',
    ),
    'parents' => 
    array (
      0 => 'ilblogpostinggui',
      1 => 'ildclcreateviewdefinitiongui',
      2 => 'ildcldetailedviewdefinitiongui',
      3 => 'ildcleditviewdefinitiongui',
      4 => 'ilimprintgui',
      5 => 'illmpagegui',
      6 => 'ilobjorgunitgui',
      7 => 'ilobjportfoliogui',
      8 => 'ilobjportfoliotemplategui',
      9 => 'ilpagelayoutgui',
      10 => 'ilportfoliopagegui',
      11 => 'ilportfoliotemplatepagegui',
      12 => 'ilwikipagegui',
    ),
  ),
  'ilparticipantsperassignmenttablegui' => 
  array (
    'cid' => 'vm',
    'class_name' => 'ilParticipantsPerAssignmentTableGUI',
    'class_path' => './components/ILIAS/Exercise/classes/class.ilParticipantsPerAssignmentTableGUI.php',
    'children' => 
    array (
      0 => 'ilformpropertydispatchgui',
    ),
    'parents' => 
    array (
      0 => 'ilexercisemanagementgui',
    ),
  ),
  'ilparticipantstestresultsgui' => 
  array (
    'cid' => 'vn',
    'class_name' => 'ilParticipantsTestResultsGUI',
    'class_path' => './components/ILIAS/Test/classes/class.ilParticipantsTestResultsGUI.php',
    'children' => 
    array (
      0 => 'iltestevaluationgui',
      1 => 'ilassquestionpagegui',
      2 => 'ilassspecfeedbackpagegui',
      3 => 'ilassgenfeedbackpagegui',
    ),
    'parents' => 
    array (
      0 => 'ilobjtestgui',
      1 => 'iltestexportgui',
      2 => 'iltestresultsgui',
    ),
  ),
  'ilpasswordassistancegui' => 
  array (
    'cid' => 'vp',
    'class_name' => 'ilPasswordAssistanceGUI',
    'class_path' => './Services/Init/classes/class.ilPasswordAssistanceGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilstartupgui',
    ),
  ),
  'ilpermissiongui' => 
  array (
    'cid' => 'vu',
    'class_name' => 'ilPermissionGUI',
    'class_path' => './Services/AccessControl/classes/class.ilPermissionGUI.php',
    'children' => 
    array (
      0 => 'ilobjrolegui',
      1 => 'ilrepositorysearchgui',
      2 => 'ilobjectpermissionstatusgui',
      3 => 'ildidactictemplategui',
    ),
    'parents' => 
    array (
      0 => 'iladministrationgui',
      1 => 'ilobjaccessibilitysettingsgui',
      2 => 'ilobjadministrativenotificationgui',
      3 => 'ilobjadvancededitinggui',
      4 => 'ilobjassessmentfoldergui',
      5 => 'ilobjauthsettingsgui',
      6 => 'ilobjawarenessadministrationgui',
      7 => 'ilobjbadgeadministrationgui',
      8 => 'ilobjbibliographicadmingui',
      9 => 'ilobjbibliographicgui',
      10 => 'ilobjblogadministrationgui',
      11 => 'ilobjbloggui',
      12 => 'ilobjbookingpoolgui',
      13 => 'ilobjcalendarsettingsgui',
      14 => 'ilobjcategorygui',
      15 => 'ilobjcategoryreferencegui',
      16 => 'ilobjcertificatesettingsgui',
      17 => 'ilobjchatroomadmingui',
      18 => 'ilobjchatroomgui',
      19 => 'ilobjcloudgui',
      20 => 'ilobjcmixapiadministrationgui',
      21 => 'ilobjcmixapigui',
      22 => 'ilobjcommentssettingsgui',
      23 => 'ilobjcomponentsettingsgui',
      24 => 'ilobjcontactadministrationgui',
      25 => 'ilobjcontentobjectgui',
      26 => 'ilobjcontentpageadministrationgui',
      27 => 'ilobjcontentpagegui',
      28 => 'ilobjcourseadministrationgui',
      29 => 'ilobjcoursegui',
      30 => 'ilobjcoursereferencegui',
      31 => 'ilobjdashboardsettingsgui',
      32 => 'ilobjdatacollectiongui',
      33 => 'ilobjdataprotectiongui',
      34 => 'ilobjecssettingsgui',
      35 => 'ilobjemployeetalkgui',
      36 => 'ilobjemployeetalkseriesgui',
      37 => 'ilobjexerciseadministrationgui',
      38 => 'ilobjexercisegui',
      39 => 'ilobjexternaltoolssettingsgui',
      40 => 'ilobjfileaccesssettingsgui',
      41 => 'ilobjfilebasedlmgui',
      42 => 'ilobjfilegui',
      43 => 'ilobjfileservicesgui',
      44 => 'ilobjfoldergui',
      45 => 'ilobjforumadministrationgui',
      46 => 'ilobjforumgui',
      47 => 'ilobjglossarygui',
      48 => 'ilobjgroupadministrationgui',
      49 => 'ilobjgroupgui',
      50 => 'ilobjgroupreferencegui',
      51 => 'ilobjhelpsettingsgui',
      52 => 'ilobjindividualassessmentgui',
      53 => 'ilobjitemgroupgui',
      54 => 'ilobjltiadministrationgui',
      55 => 'ilobjlticonsumergui',
      56 => 'ilobjlanguagefoldergui',
      57 => 'ilobjlearninghistorysettingsgui',
      58 => 'ilobjlearningmodulegui',
      59 => 'ilobjlearningresourcessettingsgui',
      60 => 'ilobjlearningsequenceadmingui',
      61 => 'ilobjlearningsequencegui',
      62 => 'ilobjlegalnoticegui',
      63 => 'ilobjlinkresourcegui',
      64 => 'ilobjloggingsettingsgui',
      65 => 'ilobjmdsettingsgui',
      66 => 'ilobjmailgui',
      67 => 'ilobjmainmenugui',
      68 => 'ilobjmediacastgui',
      69 => 'ilobjmediacastsettingsgui',
      70 => 'ilobjmediaobjectssettingsgui',
      71 => 'ilobjmediapoolgui',
      72 => 'ilobjnewssettingsgui',
      73 => 'ilobjnotessettingsgui',
      74 => 'ilobjnotificationadmingui',
      75 => 'ilobjobjectfoldergui',
      76 => 'ilobjobjecttemplateadministrationgui',
      77 => 'ilobjorgunitgui',
      78 => 'ilobjpersonalworkspacesettingsgui',
      79 => 'ilobjpollgui',
      80 => 'ilobjportfolioadministrationgui',
      81 => 'ilobjportfoliotemplategui',
      82 => 'ilobjprivacysecuritygui',
      83 => 'ilobjquestionpoolgui',
      84 => 'ilobjrecoveryfoldergui',
      85 => 'ilobjremotecategorygui',
      86 => 'ilobjremotecoursegui',
      87 => 'ilobjremotefilegui',
      88 => 'ilobjremoteglossarygui',
      89 => 'ilobjremotegroupgui',
      90 => 'ilobjremotelearningmodulegui',
      91 => 'ilobjremotetestgui',
      92 => 'ilobjremotewikigui',
      93 => 'ilobjrepositorysettingsgui',
      94 => 'ilobjrolefoldergui',
      95 => 'ilobjrootfoldergui',
      96 => 'ilobjsahslearningmodulegui',
      97 => 'ilobjscorm2004learningmodulegui',
      98 => 'ilobjscormlearningmodulegui',
      99 => 'ilobjsearchsettingsgui',
      100 => 'ilobjsessiongui',
      101 => 'ilobjskillmanagementgui',
      102 => 'ilobjskilltreegui',
      103 => 'ilobjstudyprogrammeadmingui',
      104 => 'ilobjstudyprogrammegui',
      105 => 'ilobjstudyprogrammereferencegui',
      106 => 'ilobjstylesettingsgui',
      107 => 'ilobjsurveyadministrationgui',
      108 => 'ilobjsurveygui',
      109 => 'ilobjsurveyquestionpoolgui',
      110 => 'ilobjsystemcheckgui',
      111 => 'ilobjsystemfoldergui',
      112 => 'ilobjtaggingsettingsgui',
      113 => 'ilobjtalktemplateadministrationgui',
      114 => 'ilobjtalktemplategui',
      115 => 'ilobjtaxonomyadministrationgui',
      116 => 'ilobjtermsofservicegui',
      117 => 'ilobjtestgui',
      118 => 'ilobjuserfoldergui',
      119 => 'ilobjusertrackinggui',
      120 => 'ilobjwebdavgui',
      121 => 'ilobjwebresourceadministrationgui',
      122 => 'ilobjwikigui',
      123 => 'ilobjwikisettingsgui',
      124 => 'ilrepositorygui',
    ),
  ),
  'ilpersonalprofilegui' => 
  array (
    'cid' => 'vv',
    'class_name' => 'ilPersonalProfileGUI',
    'class_path' => './Services/User/classes/Profile/class.ilPersonalProfileGUI.php',
    'children' => 
    array (
      0 => 'ilpublicuserprofilegui',
      1 => 'iluserprivacysettingsgui',
      2 => 'illegaldocumentsagreementgui',
      3 => 'illegaldocumentswithdrawalgui',
    ),
    'parents' => 
    array (
      0 => 'ildashboardgui',
    ),
  ),
  'ilpersonalsettingsgui' => 
  array (
    'cid' => 'vw',
    'class_name' => 'ilPersonalSettingsGUI',
    'class_path' => './Services/User/classes/Settings/class.ilPersonalSettingsGUI.php',
    'children' => 
    array (
      0 => 'ilmailoptionsgui',
    ),
    'parents' => 
    array (
      0 => 'ildashboardgui',
    ),
  ),
  'ilpersonalskillsgui' => 
  array (
    'cid' => 'vz',
    'class_name' => 'ilPersonalSkillsGUI',
    'class_path' => './Services/Skill/Personal/class.ilPersonalSkillsGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilachievementsgui',
      1 => 'ilcontskillpresentationgui',
    ),
  ),
  'ilpersonalworkspacegui' => 
  array (
    'cid' => 'w0',
    'class_name' => 'ilPersonalWorkspaceGUI',
    'class_path' => './Services/PersonalWorkspace/classes/class.ilPersonalWorkspaceGUI.php',
    'children' => 
    array (
      0 => 'ilobjworkspacerootfoldergui',
      1 => 'ilobjworkspacefoldergui',
      2 => 'ilobjectcopygui',
      3 => 'ilobjfilegui',
      4 => 'ilobjbloggui',
      5 => 'ilobjtestverificationgui',
      6 => 'ilobjexerciseverificationgui',
      7 => 'ilobjlinkresourcegui',
      8 => 'ilobjcourseverificationgui',
      9 => 'ilobjscormverificationgui',
      10 => 'ilobjcmixapiverificationgui',
      11 => 'ilobjlticonsumerverificationgui',
    ),
    'parents' => 
    array (
      0 => 'ildashboardgui',
    ),
  ),
  'ilpollblockgui' => 
  array (
    'cid' => 'w4',
    'class_name' => 'ilPollBlockGUI',
    'class_path' => './components/ILIAS/Poll/classes/BlockGUI/class.ilPollBlockGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilcolumngui',
    ),
  ),
  'ilportfolioexercisegui' => 
  array (
    'cid' => 'w7',
    'class_name' => 'ilPortfolioExerciseGUI',
    'class_path' => './components/ILIAS/Portfolio/Exercise/class.ilPortfolioExerciseGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjexercisegui',
      1 => 'ilobjportfoliogui',
    ),
  ),
  'ilportfoliopagegui' => 
  array (
    'cid' => 'w8',
    'class_name' => 'ilPortfolioPageGUI',
    'class_path' => './components/ILIAS/Portfolio/Page/class.ilPortfolioPageGUI.php',
    'children' => 
    array (
      0 => 'ilpageeditorgui',
      1 => 'ileditclipboardgui',
      2 => 'ilpageobjectgui',
      3 => 'ilobjbloggui',
      4 => 'ilblogpostinggui',
      5 => 'ilpublicuserprofilegui',
      6 => 'ilcalendarmonthgui',
      7 => 'ilconsultationhoursgui',
      8 => 'illearninghistorygui',
    ),
    'parents' => 
    array (
      0 => 'ilobjbloggui',
      1 => 'ilobjfilegui',
      2 => 'ilobjportfoliogui',
    ),
  ),
  'ilportfoliorepositorygui' => 
  array (
    'cid' => 'wa',
    'class_name' => 'ilPortfolioRepositoryGUI',
    'class_path' => './components/ILIAS/Portfolio/classes/class.ilPortfolioRepositoryGUI.php',
    'children' => 
    array (
      0 => 'ilobjportfoliogui',
      1 => 'ilobjexercisegui',
    ),
    'parents' => 
    array (
      0 => 'ildashboardgui',
    ),
  ),
  'ilportfolioroleassignmentgui' => 
  array (
    'cid' => 'wb',
    'class_name' => 'ilPortfolioRoleAssignmentGUI',
    'class_path' => './components/ILIAS/Portfolio/Administration/class.ilPortfolioRoleAssignmentGUI.php',
    'children' => 
    array (
      0 => 'ilpropertyformgui',
    ),
    'parents' => 
    array (
      0 => 'ilobjportfolioadministrationgui',
    ),
  ),
  'ilportfoliotemplatepagegui' => 
  array (
    'cid' => 'we',
    'class_name' => 'ilPortfolioTemplatePageGUI',
    'class_path' => './components/ILIAS/Portfolio/Template/class.ilPortfolioTemplatePageGUI.php',
    'children' => 
    array (
      0 => 'ilpageeditorgui',
      1 => 'ileditclipboardgui',
      2 => 'ilpublicuserprofilegui',
      3 => 'ilpageobjectgui',
      4 => 'ilcalendarmonthgui',
      5 => 'ilconsultationhoursgui',
    ),
    'parents' => 
    array (
      0 => 'ilobjportfoliotemplategui',
    ),
  ),
  'ilpresentationfullgui' => 
  array (
    'cid' => 'wf',
    'class_name' => 'ilPresentationFullGUI',
    'class_path' => './components/ILIAS/Glossary/Presentation/class.ilPresentationFullGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilglossarypresentationgui',
    ),
  ),
  'ilpresentationlisttablegui' => 
  array (
    'cid' => 'wg',
    'class_name' => 'ilPresentationListTableGUI',
    'class_path' => './components/ILIAS/Glossary/Presentation/class.ilPresentationListTableGUI.php',
    'children' => 
    array (
      0 => 'ilformpropertydispatchgui',
    ),
    'parents' => 
    array (
      0 => 'ilglossarypresentationgui',
    ),
  ),
  'ilpropertyformgui' => 
  array (
    'cid' => 'wi',
    'class_name' => 'ilPropertyFormGUI',
    'class_path' => './Services/Form/classes/class.ilPropertyFormGUI.php',
    'children' => 
    array (
      0 => 'ilformpropertydispatchgui',
    ),
    'parents' => 
    array (
      0 => 'assimagemapquestiongui',
      1 => 'asskprimchoicegui',
      2 => 'asslongmenugui',
      3 => 'assorderinghorizontalgui',
      4 => 'iladministrationgui',
      5 => 'iladvancedmdsettingsgui',
      6 => 'iladvancedsearchgui',
      7 => 'ilassgenfeedbackpagegui',
      8 => 'ilasshintpagegui',
      9 => 'ilassquestionfeedbackeditinggui',
      10 => 'ilassquestionhintrequestgui',
      11 => 'ilassquestionpagegui',
      12 => 'ilassspecfeedbackpagegui',
      13 => 'ilbadgemanagementgui',
      14 => 'ilbookinggatewaygui',
      15 => 'ilbookingobjectgui',
      16 => 'ilbookingobjectservicegui',
      17 => 'ilcertificategui',
      18 => 'ilcontainerpagegui',
      19 => 'ilcontainerstartobjectspagegui',
      20 => 'ilcontentpagepagegui',
      21 => 'ilcronmanagergui',
      22 => 'ildidactictemplatesettingsgui',
      23 => 'ilexassignmenteditorgui',
      24 => 'ilforumpagegui',
      25 => 'ilglossarydefpagegui',
      26 => 'ilglossarytermgui',
      27 => 'illmpagegui',
      28 => 'illopagegui',
      29 => 'illoginpagegui',
      30 => 'illucenesearchgui',
      31 => 'ilmediacreationgui',
      32 => 'ilobjcategoryreferencegui',
      33 => 'ilobjchatroomgui',
      34 => 'ilobjcoursegui',
      35 => 'ilobjcoursereferencegui',
      36 => 'ilobjdatacollectiongui',
      37 => 'ilobjemployeetalkgui',
      38 => 'ilobjgroupgui',
      39 => 'ilobjgroupreferencegui',
      40 => 'ilobjlinkresourcegui',
      41 => 'ilobjorgunitgui',
      42 => 'ilobjquestionpoolgui',
      43 => 'ilobjquestionpoolsettingsgeneralgui',
      44 => 'ilobjsessiongui',
      45 => 'ilobjstudyprogrammeautocategoriesgui',
      46 => 'ilobjstudyprogrammeautomembershipsgui',
      47 => 'ilobjstudyprogrammereferencegui',
      48 => 'ilobjtestsettingsmaingui',
      49 => 'ilobjtestsettingsscoringresultsgui',
      50 => 'ilobjectmetadatagui',
      51 => 'ilpcamdformgui',
      52 => 'ilpcsectiongui',
      53 => 'ilpageobjectgui',
      54 => 'ilportfolioroleassignmentgui',
      55 => 'ilrepositorytrashgui',
      56 => 'ilsearchgui',
      57 => 'ilstudyprogrammerepositorysearchgui',
      58 => 'iltestpasswordprotectiongui',
      59 => 'ilwikipagegui',
    ),
  ),
  'ilpublicuserprofilegui' => 
  array (
    'cid' => 'wl',
    'class_name' => 'ilPublicUserProfileGUI',
    'class_path' => './Services/User/classes/Profile/class.ilPublicUserProfileGUI.php',
    'children' => 
    array (
      0 => 'ilobjportfoliogui',
      1 => 'ilbuddysystemgui',
    ),
    'parents' => 
    array (
      0 => 'ilassgenfeedbackpagegui',
      1 => 'ilasshintpagegui',
      2 => 'ilassquestionpagegui',
      3 => 'ilassspecfeedbackpagegui',
      4 => 'ilblogpostinggui',
      5 => 'ilcalendarpresentationgui',
      6 => 'ilconsultationhoursgui',
      7 => 'ilcontactgui',
      8 => 'ilcontainerpagegui',
      9 => 'ilcontainerstartobjectspagegui',
      10 => 'ilcontentpagepagegui',
      11 => 'ildclcreateviewdefinitiongui',
      12 => 'ildcldetailedviewdefinitiongui',
      13 => 'ildcleditviewdefinitiongui',
      14 => 'ilforumpagegui',
      15 => 'ilglossarydefpagegui',
      16 => 'ilimprintgui',
      17 => 'ilinfoscreengui',
      18 => 'illopagegui',
      19 => 'illoginpagegui',
      20 => 'illuceneusersearchgui',
      21 => 'ilmdcopyrightusagegui',
      22 => 'ilmailfoldergui',
      23 => 'ilmediapoolpagegui',
      24 => 'ilobjbookingpoolgui',
      25 => 'ilobjcoursegui',
      26 => 'ilobjforumgui',
      27 => 'ilobjgroupgui',
      28 => 'ilobjwikigui',
      29 => 'ilpagelayoutgui',
      30 => 'ilpageobjectgui',
      31 => 'ilpersonalprofilegui',
      32 => 'ilportfoliopagegui',
      33 => 'ilportfoliotemplatepagegui',
      34 => 'iltestexpresspageobjectgui',
      35 => 'iltestpagegui',
      36 => 'ilusersgallerygui',
      37 => 'ilwikipagegui',
      38 => 'ilworkspaceaccessgui',
    ),
  ),
  'ilquestionbrowsertablegui' => 
  array (
    'cid' => 'wm',
    'class_name' => 'ilQuestionBrowserTableGUI',
    'class_path' => './components/ILIAS/TestQuestionPool/classes/tables/class.ilQuestionBrowserTableGUI.php',
    'children' => 
    array (
      0 => 'ilformpropertydispatchgui',
    ),
    'parents' => 
    array (
      0 => 'ilobjquestionpoolgui',
    ),
  ),
  'ilquestioneditgui' => 
  array (
    'cid' => 'wo',
    'class_name' => 'ilQuestionEditGUI',
    'class_path' => './components/ILIAS/TestQuestionPool/classes/class.ilQuestionEditGUI.php',
    'children' => 
    array (
      0 => 'assmultiplechoicegui',
      1 => 'assclozetestgui',
      2 => 'assmatchingquestiongui',
      3 => 'asskprimchoicegui',
      4 => 'assorderingquestiongui',
      5 => 'assimagemapquestiongui',
      6 => 'assnumericgui',
      7 => 'asstextsubsetgui',
      8 => 'asssinglechoicegui',
      9 => 'asstextquestiongui',
      10 => 'asserrortextgui',
      11 => 'assorderinghorizontalgui',
      12 => 'asstextsubsetgui',
      13 => 'assformulaquestiongui',
      14 => 'asslongmenugui',
    ),
    'parents' => 
    array (
      0 => 'illmpagegui',
    ),
  ),
  'ilquestionpoolexportgui' => 
  array (
    'cid' => 'wq',
    'class_name' => 'ilQuestionPoolExportGUI',
    'class_path' => './components/ILIAS/TestQuestionPool/classes/class.ilQuestionPoolExportGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjquestionpoolgui',
    ),
  ),
  'ilquestionpoolskilladministrationgui' => 
  array (
    'cid' => 'wu',
    'class_name' => 'ilQuestionPoolSkillAdministrationGUI',
    'class_path' => './components/ILIAS/TestQuestionPool/classes/class.ilQuestionPoolSkillAdministrationGUI.php',
    'children' => 
    array (
      0 => 'ilassquestionskillassignmentsgui',
      1 => 'ilassquestionskillusagestablegui',
    ),
    'parents' => 
    array (
      0 => 'ilobjquestionpoolgui',
    ),
  ),
  'ilratingcategorygui' => 
  array (
    'cid' => 'wz',
    'class_name' => 'ilRatingCategoryGUI',
    'class_path' => './Services/Rating/classes/class.ilRatingCategoryGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilratinggui',
    ),
  ),
  'ilratinggui' => 
  array (
    'cid' => 'x1',
    'class_name' => 'ilRatingGUI',
    'class_path' => './Services/Rating/classes/class.ilRatingGUI.php',
    'children' => 
    array (
      0 => 'ilratingcategorygui',
    ),
    'parents' => 
    array (
      0 => 'ilblogpostinggui',
      1 => 'ilcommonactiondispatchergui',
      2 => 'ilexpeerreviewgui',
      3 => 'illmpresentationgui',
      4 => 'ilobjdatacollectiongui',
      5 => 'ilobjforumgui',
      6 => 'ilobjwikigui',
      7 => 'iltestexpresspageobjectgui',
      8 => 'ilwikipagegui',
    ),
  ),
  'ilrecommendedcontentroleconfiggui' => 
  array (
    'cid' => 'x3',
    'class_name' => 'ilRecommendedContentRoleConfigGUI',
    'class_path' => './Services/Repository/RecommendedContent/classes/class.ilRecommendedContentRoleConfigGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjrolegui',
    ),
  ),
  'ilregistrationsettingsgui' => 
  array (
    'cid' => 'x9',
    'class_name' => 'ilRegistrationSettingsGUI',
    'class_path' => './Services/Registration/classes/class.ilRegistrationSettingsGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjauthsettingsgui',
    ),
  ),
  'ilrepostandarduploadhandlergui' => 
  array (
    'cid' => 'xd',
    'class_name' => 'ilRepoStandardUploadHandlerGUI',
    'class_path' => './Services/Repository/Service/Form/class.ilRepoStandardUploadHandlerGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilexsubmissionfilegui',
      1 => 'ilmediacreationgui',
      2 => 'ilobjmediapoolgui',
      3 => 'ilpcinteractiveimagegui',
      4 => 'ilpcsourcecodegui',
    ),
  ),
  'ilrepositorygui' => 
  array (
    'cid' => 'xf',
    'class_name' => 'ilRepositoryGUI',
    'class_path' => './Services/Repository/classes/class.ilRepositoryGUI.php',
    'children' => 
    array (
      0 => 'ilobjgroupgui',
      1 => 'ilobjfoldergui',
      2 => 'ilobjfilegui',
      3 => 'ilobjcoursegui',
      4 => 'ilcourseobjectivesgui',
      5 => 'ilobjsahslearningmodulegui',
      6 => 'ilobjchatroomgui',
      7 => 'ilobjforumgui',
      8 => 'ilobjlearningmodulegui',
      9 => 'ilobjglossarygui',
      10 => 'ilobjquestionpoolgui',
      11 => 'ilobjsurveyquestionpoolgui',
      12 => 'ilobjtestgui',
      13 => 'ilobjsurveygui',
      14 => 'ilobjexercisegui',
      15 => 'ilobjmediapoolgui',
      16 => 'ilobjfilebasedlmgui',
      17 => 'ilobjcategorygui',
      18 => 'ilobjrolegui',
      19 => 'ilobjbloggui',
      20 => 'ilobjlinkresourcegui',
      21 => 'ilobjrootfoldergui',
      22 => 'ilobjmediacastgui',
      23 => 'ilobjremotecoursegui',
      24 => 'ilobjsessiongui',
      25 => 'ilobjcoursereferencegui',
      26 => 'ilobjcategoryreferencegui',
      27 => 'ilobjdatacollectiongui',
      28 => 'ilobjgroupreferencegui',
      29 => 'ilobjstudyprogrammereferencegui',
      30 => 'ilobjpollgui',
      31 => 'ilobjremotecategorygui',
      32 => 'ilobjremotewikigui',
      33 => 'ilobjremotelearningmodulegui',
      34 => 'ilobjremoteglossarygui',
      35 => 'ilobjremotefilegui',
      36 => 'ilobjremotegroupgui',
      37 => 'ilobjremotetestgui',
      38 => 'ilobjcloudgui',
      39 => 'ilobjportfoliotemplategui',
      40 => 'ilobjstudyprogrammegui',
      41 => 'ilobjindividualassessmentgui',
      42 => 'ilobjlticonsumergui',
      43 => 'ilobjcmixapigui',
      44 => 'ilpermissiongui',
      45 => 'ilobjbibliographicgui',
      46 => 'ilobjbibliographicuploadhandlergui',
      47 => 'ilobjbookingpoolgui',
      48 => 'ilobjchatroomadmingui',
      49 => 'ilobjcontentpagegui',
      50 => 'ilobjfileuploadhandlergui',
      51 => 'ilobjitemgroupgui',
      52 => 'ilobjlearningsequencegui',
      53 => 'ilobjlegaldocumentsgui',
      54 => 'ilobjwikigui',
      55 => 'iltestpagegui',
    ),
    'parents' => 
    array (
    ),
  ),
  'ilrepositoryobjectsearchgui' => 
  array (
    'cid' => 'xi',
    'class_name' => 'ilRepositoryObjectSearchGUI',
    'class_path' => './Services/Search/classes/class.ilRepositoryObjectSearchGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjforumgui',
      1 => 'ilobjwikigui',
    ),
  ),
  'ilrepositorysearchgui' => 
  array (
    'cid' => 'xk',
    'class_name' => 'ilRepositorySearchGUI',
    'class_path' => './Services/Search/classes/class.ilRepositorySearchGUI.php',
    'children' => 
    array (
      0 => 'ilformpropertydispatchgui',
    ),
    'parents' => 
    array (
      0 => 'ilbookingparticipantgui',
      1 => 'ilconsultationhoursgui',
      2 => 'ilcoursemembershipgui',
      3 => 'ilexsubmissionteamgui',
      4 => 'ilexercisemanagementgui',
      5 => 'ilforummoderatorsgui',
      6 => 'ilgroupmembershipgui',
      7 => 'ilindividualassessmentmembersgui',
      8 => 'illearningsequencemembershipgui',
      9 => 'ilobjbloggui',
      10 => 'ilobjcoursegui',
      11 => 'ilobjemployeetalkgui',
      12 => 'ilobjemployeetalkseriesgui',
      13 => 'ilobjrolegui',
      14 => 'ilobjtestgui',
      15 => 'ilobjuserfoldergui',
      16 => 'ilobjectpermissionstatusgui',
      17 => 'ilorgunituserassignmentgui',
      18 => 'ilpermissiongui',
      19 => 'ilsessionmembershipgui',
      20 => 'ilskillprofilegui',
      21 => 'ilsurveyparticipantsgui',
      22 => 'ilsurveyratergui',
      23 => 'iltestparticipantsgui',
    ),
  ),
  'ilrepositoryselector2inputgui' => 
  array (
    'cid' => 'xl',
    'class_name' => 'ilRepositorySelector2InputGUI',
    'class_path' => './Services/Form/classes/class.ilRepositorySelector2InputGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilformpropertydispatchgui',
    ),
  ),
  'ilrepositoryselectorexplorergui' => 
  array (
    'cid' => 'xm',
    'class_name' => 'ilRepositorySelectorExplorerGUI',
    'class_path' => './Services/Repository/classes/class.ilRepositorySelectorExplorerGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'iltestrandomquestionsetconfiggui',
    ),
  ),
  'ilrepositoryselectorinputgui' => 
  array (
    'cid' => 'xn',
    'class_name' => 'ilRepositorySelectorInputGUI',
    'class_path' => './Services/Form/classes/class.ilRepositorySelectorInputGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilformpropertydispatchgui',
    ),
  ),
  'ilrepositorytrashgui' => 
  array (
    'cid' => 'xo',
    'class_name' => 'ilRepositoryTrashGUI',
    'class_path' => './Services/Repository/Trash/class.ilRepositoryTrashGUI.php',
    'children' => 
    array (
      0 => 'ilpropertyformgui',
    ),
    'parents' => 
    array (
      0 => 'ilobjcategorygui',
      1 => 'ilobjcoursegui',
      2 => 'ilobjfoldergui',
      3 => 'ilobjrootfoldergui',
    ),
  ),
  'ilresourcecollectiongui' => 
  array (
    'cid' => 'xq',
    'class_name' => 'ilResourceCollectionGUI',
    'class_path' => './Services/ResourceStorage/classes/Collections/class.ilResourceCollectionGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilexassignmenteditorgui',
      1 => 'ilexercisemanagementgui',
      2 => 'ilresourceoverviewgui',
    ),
  ),
  'ilresourceoverviewgui' => 
  array (
    'cid' => 'xr',
    'class_name' => 'ilResourceOverviewGUI',
    'class_path' => './Services/ResourceStorage/classes/Resources/class.ilResourceOverviewGUI.php',
    'children' => 
    array (
      0 => 'ilresourcecollectiongui',
    ),
    'parents' => 
    array (
      0 => 'ilobjfileservicesgui',
    ),
  ),
  'ilsahseditgui' => 
  array (
    'cid' => 'xz',
    'class_name' => 'ilSAHSEditGUI',
    'class_path' => './components/ILIAS/ScormAicc/Editing/classes/class.ilSAHSEditGUI.php',
    'children' => 
    array (
      0 => 'ilfilesystemgui',
      1 => 'ilobjectmetadatagui',
      2 => 'ilobjscormlearningmodulegui',
      3 => 'ilinfoscreengui',
      4 => 'ilobjscorm2004learningmodulegui',
      5 => 'ilexportgui',
      6 => 'ilobjsahslearningmodulegui',
      7 => 'illtiproviderobjectsettinggui',
    ),
    'parents' => 
    array (
    ),
  ),
  'ilsahspresentationgui' => 
  array (
    'cid' => 'y0',
    'class_name' => 'ilSAHSPresentationGUI',
    'class_path' => './components/ILIAS/ScormAicc/classes/class.ilSAHSPresentationGUI.php',
    'children' => 
    array (
      0 => 'ilscormpresentationgui',
      1 => 'ilinfoscreengui',
      2 => 'ilscorm13playergui',
      3 => 'illearningprogressgui',
      4 => 'ilobjscormlearningmodulegui',
      5 => 'ilobjscorm2004learningmodulegui',
    ),
    'parents' => 
    array (
    ),
  ),
  'ilscorm13playergui' => 
  array (
    'cid' => 'y3',
    'class_name' => 'ilSCORM13PlayerGUI',
    'class_path' => './components/ILIAS/Scorm2004/classes/class.ilSCORM13PlayerGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilsahspresentationgui',
    ),
  ),
  'ilscorm2004trackingitemsperscofiltergui' => 
  array (
    'cid' => 'y4',
    'class_name' => 'ilSCORM2004TrackingItemsPerScoFilterGUI',
    'class_path' => './components/ILIAS/Scorm2004/classes/class.ilSCORM2004TrackingItemsPerScoFilterGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjscorm2004learningmodulegui',
    ),
  ),
  'ilscorm2004trackingitemsperuserfiltergui' => 
  array (
    'cid' => 'y5',
    'class_name' => 'ilSCORM2004TrackingItemsPerUserFilterGUI',
    'class_path' => './components/ILIAS/Scorm2004/classes/class.ilSCORM2004TrackingItemsPerUserFilterGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjscorm2004learningmodulegui',
    ),
  ),
  'ilscorm2004trackingitemstablegui' => 
  array (
    'cid' => 'y6',
    'class_name' => 'ilSCORM2004TrackingItemsTableGUI',
    'class_path' => './components/ILIAS/Scorm2004/classes/class.ilSCORM2004TrackingItemsTableGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjscorm2004learningmodulegui',
    ),
  ),
  'ilscormpresentationgui' => 
  array (
    'cid' => 'yc',
    'class_name' => 'ilSCORMPresentationGUI',
    'class_path' => './components/ILIAS/ScormAicc/classes/SCORM/class.ilSCORMPresentationGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilsahspresentationgui',
    ),
  ),
  'ilscormtrackingitemsperscofiltergui' => 
  array (
    'cid' => 'yg',
    'class_name' => 'ilSCORMTrackingItemsPerScoFilterGUI',
    'class_path' => './components/ILIAS/ScormAicc/classes/class.ilSCORMTrackingItemsPerScoFilterGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjscormlearningmodulegui',
    ),
  ),
  'ilscormtrackingitemsperuserfiltergui' => 
  array (
    'cid' => 'yi',
    'class_name' => 'ilSCORMTrackingItemsPerUserFilterGUI',
    'class_path' => './components/ILIAS/ScormAicc/classes/class.ilSCORMTrackingItemsPerUserFilterGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjscormlearningmodulegui',
    ),
  ),
  'ilscormtrackingitemstablegui' => 
  array (
    'cid' => 'yl',
    'class_name' => 'ilSCORMTrackingItemsTableGUI',
    'class_path' => './components/ILIAS/ScormAicc/classes/class.ilSCORMTrackingItemsTableGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjscormlearningmodulegui',
    ),
  ),
  'ilsctreetasksgui' => 
  array (
    'cid' => 'yr',
    'class_name' => 'ilSCTreeTasksGUI',
    'class_path' => './Services/Tree/classes/SystemCheck/class.ilSCTreeTasksGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjsystemcheckgui',
    ),
  ),
  'ilsamlsettingsgui' => 
  array (
    'cid' => 'yv',
    'class_name' => 'ilSamlSettingsGUI',
    'class_path' => './Services/Saml/classes/class.ilSamlSettingsGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjauthsettingsgui',
    ),
  ),
  'ilsearchbasegui' => 
  array (
    'cid' => 'yx',
    'class_name' => 'ilSearchBaseGUI',
    'class_path' => './Services/Search/classes/class.ilSearchBaseGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilsearchcontrollergui',
    ),
  ),
  'ilsearchcontrollergui' => 
  array (
    'cid' => 'yy',
    'class_name' => 'ilSearchControllerGUI',
    'class_path' => './Services/Search/classes/class.ilSearchControllerGUI.php',
    'children' => 
    array (
      0 => 'ilsearchgui',
      1 => 'iladvancedsearchgui',
      2 => 'illucenesearchgui',
      3 => 'illuceneadvancedsearchgui',
      4 => 'illuceneusersearchgui',
      5 => 'ilsearchbasegui',
    ),
    'parents' => 
    array (
    ),
  ),
  'ilsearchgui' => 
  array (
    'cid' => 'yz',
    'class_name' => 'ilSearchGUI',
    'class_path' => './Services/Search/classes/class.ilSearchGUI.php',
    'children' => 
    array (
      0 => 'ilpropertyformgui',
      1 => 'ilobjectgui',
      2 => 'ilcontainergui',
      3 => 'ilobjcategorygui',
      4 => 'ilobjcoursegui',
      5 => 'ilobjfoldergui',
      6 => 'ilobjgroupgui',
      7 => 'ilobjrootfoldergui',
      8 => 'ilobjectcopygui',
    ),
    'parents' => 
    array (
      0 => 'ilsearchcontrollergui',
    ),
  ),
  'ilselecteditemsblockgui' => 
  array (
    'cid' => 'z3',
    'class_name' => 'ilSelectedItemsBlockGUI',
    'class_path' => './Services/Dashboard/Block/classes/class.ilSelectedItemsBlockGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilcolumngui',
      1 => 'ildashboardgui',
    ),
  ),
  'ilsessionmembershipgui' => 
  array (
    'cid' => 'z6',
    'class_name' => 'ilSessionMembershipGUI',
    'class_path' => './components/ILIAS/Session/classes/class.ilSessionMembershipGUI.php',
    'children' => 
    array (
      0 => 'ilmailmembersearchgui',
      1 => 'ilusersgallerygui',
      2 => 'ilrepositorysearchgui',
      3 => 'ilsessionoverviewgui',
      4 => 'ilmemberexportgui',
    ),
    'parents' => 
    array (
      0 => 'ilobjsessiongui',
    ),
  ),
  'ilsessionoverviewgui' => 
  array (
    'cid' => 'z8',
    'class_name' => 'ilSessionOverviewGUI',
    'class_path' => './components/ILIAS/Session/classes/class.ilSessionOverviewGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilcoursemembershipgui',
      1 => 'ilgroupmembershipgui',
      2 => 'illearningsequencemembershipgui',
      3 => 'ilobjcoursegui',
      4 => 'ilobjgroupgui',
      5 => 'ilsessionmembershipgui',
    ),
  ),
  'ilsessionstatisticsgui' => 
  array (
    'cid' => 'zc',
    'class_name' => 'ilSessionStatisticsGUI',
    'class_path' => './Services/Authentication/classes/class.ilSessionStatisticsGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjusertrackinggui',
    ),
  ),
  'ilsettingspermissiongui' => 
  array (
    'cid' => 'zd',
    'class_name' => 'ilSettingsPermissionGUI',
    'class_path' => './Services/AccessControl/classes/class.ilSettingsPermissionGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjwikigui',
    ),
  ),
  'ilsharedresourcegui' => 
  array (
    'cid' => 'ze',
    'class_name' => 'ilSharedResourceGUI',
    'class_path' => './Services/PersonalWorkspace/classes/class.ilSharedResourceGUI.php',
    'children' => 
    array (
      0 => 'ilobjbloggui',
      1 => 'ilobjfilegui',
      2 => 'ilobjtestverificationgui',
      3 => 'ilobjexerciseverificationgui',
      4 => 'ilobjlinkresourcegui',
      5 => 'ilobjportfoliogui',
    ),
    'parents' => 
    array (
    ),
  ),
  'ilsingleusersharegui' => 
  array (
    'cid' => 'zh',
    'class_name' => 'ilSingleUserShareGUI',
    'class_path' => './Services/PersonalWorkspace/classes/class.ilSingleUserShareGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilworkspaceaccessgui',
    ),
  ),
  'ilskillcategorygui' => 
  array (
    'cid' => 'zj',
    'class_name' => 'ilSkillCategoryGUI',
    'class_path' => './Services/Skill/Node/class.ilSkillCategoryGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjskillmanagementgui',
      1 => 'ilobjskilltreegui',
    ),
  ),
  'ilskillprofilegui' => 
  array (
    'cid' => 'zl',
    'class_name' => 'ilSkillProfileGUI',
    'class_path' => './Services/Skill/Profile/class.ilSkillProfileGUI.php',
    'children' => 
    array (
      0 => 'ilrepositorysearchgui',
    ),
    'parents' => 
    array (
      0 => 'ilcontskilladmingui',
      1 => 'ilobjskilltreegui',
    ),
  ),
  'ilskillprofileuploadhandlergui' => 
  array (
    'cid' => 'zn',
    'class_name' => 'ilSkillProfileUploadHandlerGUI',
    'class_path' => './Services/Skill/Profile/class.ilSkillProfileUploadHandlerGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjskilltreegui',
      1 => 'ilcontskilladmingui',
    ),
  ),
  'ilskillrootgui' => 
  array (
    'cid' => 'zo',
    'class_name' => 'ilSkillRootGUI',
    'class_path' => './Services/Skill/Node/class.ilSkillRootGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjskillmanagementgui',
      1 => 'ilobjskilltreegui',
    ),
  ),
  'ilskillselectorgui' => 
  array (
    'cid' => 'zp',
    'class_name' => 'ilSkillSelectorGUI',
    'class_path' => './Services/Skill/Tree/class.ilSkillSelectorGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilassquestionskillassignmentsgui',
    ),
  ),
  'ilskilltemplatecategorygui' => 
  array (
    'cid' => 'zq',
    'class_name' => 'ilSkillTemplateCategoryGUI',
    'class_path' => './Services/Skill/Node/class.ilSkillTemplateCategoryGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjskillmanagementgui',
      1 => 'ilobjskilltreegui',
    ),
  ),
  'ilskilltemplatereferencegui' => 
  array (
    'cid' => 'zr',
    'class_name' => 'ilSkillTemplateReferenceGUI',
    'class_path' => './Services/Skill/Node/class.ilSkillTemplateReferenceGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjskillmanagementgui',
      1 => 'ilobjskilltreegui',
    ),
  ),
  'ilstartupgui' => 
  array (
    'cid' => 'zx',
    'class_name' => 'ilStartUpGUI',
    'class_path' => './Services/Init/classes/class.ilStartUpGUI.php',
    'children' => 
    array (
      0 => 'ilaccountregistrationgui',
      1 => 'ilpasswordassistancegui',
      2 => 'illoginpagegui',
      3 => 'ildashboardgui',
      4 => 'ilmembershipoverviewgui',
      5 => 'ilderivedtasksgui',
      6 => 'ilaccessibilitycontrolconceptgui',
    ),
    'parents' => 
    array (
    ),
  ),
  'ilstructureobjectgui' => 
  array (
    'cid' => 'zy',
    'class_name' => 'ilStructureObjectGUI',
    'class_path' => './components/ILIAS/LearningModule/classes/class.ilStructureObjectGUI.php',
    'children' => 
    array (
      0 => 'ilconditionhandlergui',
      1 => 'ilobjectmetadatagui',
    ),
    'parents' => 
    array (
      0 => 'ilobjcontentobjectgui',
      1 => 'ilobjlearningmodulegui',
    ),
  ),
  'ilstudyprogrammechangedeadlinegui' => 
  array (
    'cid' => '102',
    'class_name' => 'ilStudyProgrammeChangeDeadlineGUI',
    'class_path' => './components/ILIAS/StudyProgramme/classes/class.ilStudyProgrammeChangeDeadlineGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjstudyprogrammemembersgui',
    ),
  ),
  'ilstudyprogrammechangeexpiredategui' => 
  array (
    'cid' => '103',
    'class_name' => 'ilStudyProgrammeChangeExpireDateGUI',
    'class_path' => './components/ILIAS/StudyProgramme/classes/class.ilStudyProgrammeChangeExpireDateGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjstudyprogrammemembersgui',
    ),
  ),
  'ilstudyprogrammecommonsettingsgui' => 
  array (
    'cid' => '104',
    'class_name' => 'ilStudyProgrammeCommonSettingsGUI',
    'class_path' => './components/ILIAS/StudyProgramme/classes/class.ilStudyProgrammeCommonSettingsGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjstudyprogrammesettingsgui',
    ),
  ),
  'ilstudyprogrammedashboardviewgui' => 
  array (
    'cid' => '106',
    'class_name' => 'ilStudyProgrammeDashboardViewGUI',
    'class_path' => './components/ILIAS/StudyProgramme/classes/class.ilStudyProgrammeDashboardViewGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ildashboardgui',
    ),
  ),
  'ilstudyprogrammemailmembersearchgui' => 
  array (
    'cid' => '10a',
    'class_name' => 'ilStudyProgrammeMailMemberSearchGUI',
    'class_path' => './components/ILIAS/StudyProgramme/classes/class.ilStudyProgrammeMailMemberSearchGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjstudyprogrammemembersgui',
    ),
  ),
  'ilstudyprogrammerepositorysearchgui' => 
  array (
    'cid' => '10e',
    'class_name' => 'ilStudyProgrammeRepositorySearchGUI',
    'class_path' => './components/ILIAS/StudyProgramme/classes/class.ilStudyProgrammeRepositorySearchGUI.php',
    'children' => 
    array (
      0 => 'ilpropertyformgui',
    ),
    'parents' => 
    array (
      0 => 'ilobjstudyprogrammemembersgui',
    ),
  ),
  'ilstudyprogrammetypegui' => 
  array (
    'cid' => '10h',
    'class_name' => 'ilStudyProgrammeTypeGUI',
    'class_path' => './components/ILIAS/StudyProgramme/classes/types/class.ilStudyProgrammeTypeGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjstudyprogrammeadmingui',
    ),
  ),
  'ilstylecharacteristicgui' => 
  array (
    'cid' => '10j',
    'class_name' => 'ilStyleCharacteristicGUI',
    'class_path' => './Services/Style/Content/Characteristic/class.ilStyleCharacteristicGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjstylesheetgui',
    ),
  ),
  'ilsurveyconstraintsgui' => 
  array (
    'cid' => '10y',
    'class_name' => 'ilSurveyConstraintsGUI',
    'class_path' => './components/ILIAS/Survey/Constraints/class.ilSurveyConstraintsGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjsurveygui',
    ),
  ),
  'ilsurveyeditorgui' => 
  array (
    'cid' => '110',
    'class_name' => 'ilSurveyEditorGUI',
    'class_path' => './components/ILIAS/Survey/Editing/class.ilSurveyEditorGUI.php',
    'children' => 
    array (
      0 => 'surveymultiplechoicequestiongui',
      1 => 'surveymetricquestiongui',
      2 => 'surveysinglechoicequestiongui',
      3 => 'surveytextquestiongui',
      4 => 'surveymatrixquestiongui',
    ),
    'parents' => 
    array (
      0 => 'ilobjsurveygui',
    ),
  ),
  'ilsurveyevaluationgui' => 
  array (
    'cid' => '111',
    'class_name' => 'ilSurveyEvaluationGUI',
    'class_path' => './components/ILIAS/Survey/Evaluation/class.ilSurveyEvaluationGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjsurveygui',
    ),
  ),
  'ilsurveyexecutiongui' => 
  array (
    'cid' => '112',
    'class_name' => 'ilSurveyExecutionGUI',
    'class_path' => './components/ILIAS/Survey/Execution/class.ilSurveyExecutionGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjsurveygui',
    ),
  ),
  'ilsurveyparticipantsgui' => 
  array (
    'cid' => '114',
    'class_name' => 'ilSurveyParticipantsGUI',
    'class_path' => './components/ILIAS/Survey/Participants/class.ilSurveyParticipantsGUI.php',
    'children' => 
    array (
      0 => 'ilrepositorysearchgui',
      1 => 'ilsurveyratergui',
    ),
    'parents' => 
    array (
      0 => 'ilobjsurveygui',
    ),
  ),
  'ilsurveyratergui' => 
  array (
    'cid' => '11b',
    'class_name' => 'ilSurveyRaterGUI',
    'class_path' => './components/ILIAS/Survey/Participants/class.ilSurveyRaterGUI.php',
    'children' => 
    array (
      0 => 'ilrepositorysearchgui',
    ),
    'parents' => 
    array (
      0 => 'ilsurveyparticipantsgui',
    ),
  ),
  'ilsurveyskilldeterminationgui' => 
  array (
    'cid' => '11g',
    'class_name' => 'ilSurveySkillDeterminationGUI',
    'class_path' => './components/ILIAS/Survey/Skills/class.ilSurveySkillDeterminationGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjsurveygui',
    ),
  ),
  'ilsurveyskillgui' => 
  array (
    'cid' => '11h',
    'class_name' => 'ilSurveySkillGUI',
    'class_path' => './components/ILIAS/Survey/Skills/class.ilSurveySkillGUI.php',
    'children' => 
    array (
      0 => 'ilsurveyskillthresholdsgui',
    ),
    'parents' => 
    array (
      0 => 'ilobjsurveygui',
    ),
  ),
  'ilsurveyskillthresholdsgui' => 
  array (
    'cid' => '11j',
    'class_name' => 'ilSurveySkillThresholdsGUI',
    'class_path' => './components/ILIAS/Survey/Skills/class.ilSurveySkillThresholdsGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilsurveyskillgui',
    ),
  ),
  'ilsystemstyleconfiggui' => 
  array (
    'cid' => '11n',
    'class_name' => 'ilSystemStyleConfigGUI',
    'class_path' => './Services/Style/System/classes/Config/class.ilSystemStyleConfigGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilsystemstylemaingui',
    ),
  ),
  'ilsystemstyledocumentationgui' => 
  array (
    'cid' => '11p',
    'class_name' => 'ilSystemStyleDocumentationGUI',
    'class_path' => './Services/Style/System/classes/Documentation/class.ilSystemStyleDocumentationGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilsystemstylemaingui',
    ),
  ),
  'ilsystemstyleiconsgui' => 
  array (
    'cid' => '11q',
    'class_name' => 'ilSystemStyleIconsGUI',
    'class_path' => './Services/Style/System/classes/Icons/class.ilSystemStyleIconsGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilsystemstylemaingui',
    ),
  ),
  'ilsystemstylemaingui' => 
  array (
    'cid' => '11r',
    'class_name' => 'ilSystemStyleMainGUI',
    'class_path' => './Services/Style/System/classes/class.ilSystemStyleMainGUI.php',
    'children' => 
    array (
      0 => 'ilsystemstyleoverviewgui',
      1 => 'ilsystemstyleconfiggui',
      2 => 'ilsystemstylescssgui',
      3 => 'ilsystemstyleiconsgui',
      4 => 'ilsystemstyledocumentationgui',
    ),
    'parents' => 
    array (
      0 => 'ilobjstylesettingsgui',
    ),
  ),
  'ilsystemstyleoverviewgui' => 
  array (
    'cid' => '11s',
    'class_name' => 'ilSystemStyleOverviewGUI',
    'class_path' => './Services/Style/System/classes/Overview/class.ilSystemStyleOverviewGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilsystemstylemaingui',
    ),
  ),
  'ilsystemstylescssgui' => 
  array (
    'cid' => '11t',
    'class_name' => 'ilSystemStyleScssGUI',
    'class_path' => './Services/Style/System/classes/Scss/class.ilSystemStyleScssGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilsystemstylemaingui',
    ),
  ),
  'ilsystemsupportcontactsgui' => 
  array (
    'cid' => '11v',
    'class_name' => 'ilSystemSupportContactsGUI',
    'class_path' => './components/ILIAS/SystemFolder/classes/class.ilSystemSupportContactsGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
    ),
  ),
  'iltablepropertiesstoragegui' => 
  array (
    'cid' => '122',
    'class_name' => 'ilTablePropertiesStorageGUI',
    'class_path' => './Services/Table/classes/class.ilTablePropertiesStorageGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
    ),
  ),
  'iltagginggui' => 
  array (
    'cid' => '126',
    'class_name' => 'ilTaggingGUI',
    'class_path' => './Services/Tagging/classes/class.ilTaggingGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilcommonactiondispatchergui',
    ),
  ),
  'iltaggingslatecontentgui' => 
  array (
    'cid' => '127',
    'class_name' => 'ilTaggingSlateContentGUI',
    'class_path' => './Services/Tagging/classes/class.ilTaggingSlateContentGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
    ),
  ),
  'iltaxmdgui' => 
  array (
    'cid' => '12a',
    'class_name' => 'ilTaxMDGUI',
    'class_path' => './Services/Taxonomy/classes/class.ilTaxMDGUI.php',
    'children' => 
    array (
      0 => 'ilformpropertydispatchgui',
    ),
    'parents' => 
    array (
      0 => 'ilobjectmetadatagui',
    ),
  ),
  'iltaxselectinputgui' => 
  array (
    'cid' => '12b',
    'class_name' => 'ilTaxSelectInputGUI',
    'class_path' => './Services/Taxonomy/classes/class.ilTaxSelectInputGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilformpropertydispatchgui',
    ),
  ),
  'iltaxonomysettingsgui' => 
  array (
    'cid' => '12f',
    'class_name' => 'ilTaxonomySettingsGUI',
    'class_path' => './Services/Taxonomy/Settings/class.ilTaxonomySettingsGUI.php',
    'children' => 
    array (
      0 => 'ilobjtaxonomygui',
    ),
    'parents' => 
    array (
      0 => 'ilobjcategorygui',
      1 => 'ilobjglossarygui',
      2 => 'ilobjquestionpoolgui',
    ),
  ),
  'iltermdefinitionbulkcreationgui' => 
  array (
    'cid' => '12h',
    'class_name' => 'ilTermDefinitionBulkCreationGUI',
    'class_path' => './components/ILIAS/Glossary/Term/class.ilTermDefinitionBulkCreationGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjglossarygui',
    ),
  ),
  'iltermdefinitioneditorgui' => 
  array (
    'cid' => '12j',
    'class_name' => 'ilTermDefinitionEditorGUI',
    'class_path' => './components/ILIAS/Glossary/Term/class.ilTermDefinitionEditorGUI.php',
    'children' => 
    array (
      0 => 'ilglossarydefpagegui',
    ),
    'parents' => 
    array (
      0 => 'ilglossarytermgui',
    ),
  ),
  'iltestansweroptionalquestionsconfirmationgui' => 
  array (
    'cid' => '12o',
    'class_name' => 'ilTestAnswerOptionalQuestionsConfirmationGUI',
    'class_path' => './components/ILIAS/Test/classes/confirmations/class.ilTestAnswerOptionalQuestionsConfirmationGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'iltestplayerfixedquestionsetgui',
      1 => 'iltestplayerrandomquestionsetgui',
    ),
  ),
  'iltestcorrectionsgui' => 
  array (
    'cid' => '12q',
    'class_name' => 'ilTestCorrectionsGUI',
    'class_path' => './components/ILIAS/Test/classes/class.ilTestCorrectionsGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjtestgui',
    ),
  ),
  'iltestdashboardgui' => 
  array (
    'cid' => '12r',
    'class_name' => 'ilTestDashboardGUI',
    'class_path' => './components/ILIAS/Test/classes/class.ilTestDashboardGUI.php',
    'children' => 
    array (
      0 => 'iltestparticipantsgui',
      1 => 'iltestparticipantstimeextensiongui',
    ),
    'parents' => 
    array (
      0 => 'ilobjtestgui',
    ),
  ),
  'iltestevalobjectiveorientedgui' => 
  array (
    'cid' => '12t',
    'class_name' => 'ilTestEvalObjectiveOrientedGUI',
    'class_path' => './components/ILIAS/Test/classes/class.ilTestEvalObjectiveOrientedGUI.php',
    'children' => 
    array (
      0 => 'ilassquestionpagegui',
      1 => 'iltestresultstoolbargui',
    ),
    'parents' => 
    array (
      0 => 'iltestresultsgui',
    ),
  ),
  'iltestevaluationgui' => 
  array (
    'cid' => '12u',
    'class_name' => 'ilTestEvaluationGUI',
    'class_path' => './components/ILIAS/Test/classes/class.ilTestEvaluationGUI.php',
    'children' => 
    array (
      0 => 'iltestpassdetailsoverviewtablegui',
      1 => 'iltestresultstoolbargui',
      2 => 'iltestpassdeletionconfirmationgui',
    ),
    'parents' => 
    array (
      0 => 'ilmytestresultsgui',
      1 => 'ilmytestsolutionsgui',
      2 => 'ilobjtestgui',
      3 => 'ilparticipantstestresultsgui',
      4 => 'iltestparticipantsgui',
    ),
  ),
  'iltestexportgui' => 
  array (
    'cid' => '12v',
    'class_name' => 'ilTestExportGUI',
    'class_path' => './components/ILIAS/Test/classes/class.ilTestExportGUI.php',
    'children' => 
    array (
      0 => 'ilparticipantstestresultsgui',
    ),
    'parents' => 
    array (
      0 => 'ilobjtestgui',
    ),
  ),
  'iltestexpresspageobjectgui' => 
  array (
    'cid' => '12x',
    'class_name' => 'ilTestExpressPageObjectGUI',
    'class_path' => './components/ILIAS/Test/classes/class.ilTestExpressPageObjectGUI.php',
    'children' => 
    array (
      0 => 'assmultiplechoicegui',
      1 => 'assclozetestgui',
      2 => 'assmatchingquestiongui',
      3 => 'assorderingquestiongui',
      4 => 'assimagemapquestiongui',
      5 => 'assnumericgui',
      6 => 'asstextsubsetgui',
      7 => 'asssinglechoicegui',
      8 => 'asstextquestiongui',
      9 => 'assformulaquestiongui',
      10 => 'ilpageeditorgui',
      11 => 'ileditclipboardgui',
      12 => 'ilratinggui',
      13 => 'ilpublicuserprofilegui',
      14 => 'ilassquestionpagegui',
      15 => 'ilnotegui',
      16 => 'ilobjquestionpoolgui',
    ),
    'parents' => 
    array (
      0 => 'assmultiplechoicegui',
      1 => 'assclozetestgui',
      2 => 'assmatchingquestiongui',
      3 => 'assorderingquestiongui',
      4 => 'assimagemapquestiongui',
      5 => 'assnumericgui',
      6 => 'asstextsubsetgui',
      7 => 'asssinglechoicegui',
      8 => 'asstextquestiongui',
      9 => 'assformulaquestiongui',
      10 => 'ilobjtestgui',
      11 => 'iltestfixedquestionsetconfiggui',
    ),
  ),
  'iltestfixedquestionsetconfiggui' => 
  array (
    'cid' => '12y',
    'class_name' => 'ilTestFixedQuestionSetConfigGUI',
    'class_path' => './components/ILIAS/Test/classes/class.ilTestFixedQuestionSetConfigGUI.php',
    'children' => 
    array (
      0 => 'iltestexpresspageobjectgui',
      1 => 'ilpageeditorgui',
      2 => 'ilassquestionpagegui',
    ),
    'parents' => 
    array (
      0 => 'ilobjtestgui',
    ),
  ),
  'iltestinfoscreentoolbargui' => 
  array (
    'cid' => '130',
    'class_name' => 'ilTestInfoScreenToolbarGUI',
    'class_path' => './components/ILIAS/Test/classes/toolbars/class.ilTestInfoScreenToolbarGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjtestgui',
    ),
  ),
  'iltestpagegui' => 
  array (
    'cid' => '136',
    'class_name' => 'ilTestPageGUI',
    'class_path' => './components/ILIAS/Test/classes/PageEditor/class.ilTestPageGUI.php',
    'children' => 
    array (
      0 => 'ilpageeditorgui',
      1 => 'ileditclipboardgui',
      2 => 'ilmdeditorgui',
      3 => 'ilpublicuserprofilegui',
      4 => 'ilnotegui',
    ),
    'parents' => 
    array (
      0 => 'ilrepositorygui',
      1 => 'ilobjtestgui',
    ),
  ),
  'iltestparticipantsgui' => 
  array (
    'cid' => '137',
    'class_name' => 'ilTestParticipantsGUI',
    'class_path' => './components/ILIAS/Test/classes/class.ilTestParticipantsGUI.php',
    'children' => 
    array (
      0 => 'iltestparticipantstablegui',
      1 => 'ilrepositorysearchgui',
      2 => 'iltestevaluationgui',
    ),
    'parents' => 
    array (
      0 => 'iltestdashboardgui',
    ),
  ),
  'iltestparticipantstablegui' => 
  array (
    'cid' => '138',
    'class_name' => 'ilTestParticipantsTableGUI',
    'class_path' => './components/ILIAS/Test/classes/tables/class.ilTestParticipantsTableGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'iltestparticipantsgui',
    ),
  ),
  'iltestparticipantstimeextensiongui' => 
  array (
    'cid' => '139',
    'class_name' => 'ilTestParticipantsTimeExtensionGUI',
    'class_path' => './components/ILIAS/Test/classes/class.ilTestParticipantsTimeExtensionGUI.php',
    'children' => 
    array (
      0 => 'iltimingoverviewtablegui',
    ),
    'parents' => 
    array (
      0 => 'iltestdashboardgui',
    ),
  ),
  'iltestpassdeletionconfirmationgui' => 
  array (
    'cid' => '13a',
    'class_name' => 'ilTestPassDeletionConfirmationGUI',
    'class_path' => './components/ILIAS/Test/classes/confirmations/class.ilTestPassDeletionConfirmationGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'iltestevaluationgui',
    ),
  ),
  'iltestpassdetailsoverviewtablegui' => 
  array (
    'cid' => '13b',
    'class_name' => 'ilTestPassDetailsOverviewTableGUI',
    'class_path' => './components/ILIAS/Test/classes/tables/class.ilTestPassDetailsOverviewTableGUI.php',
    'children' => 
    array (
      0 => 'ilformpropertydispatchgui',
    ),
    'parents' => 
    array (
      0 => 'ilobjtestgui',
      1 => 'iltestevaluationgui',
    ),
  ),
  'iltestpasswordprotectiongui' => 
  array (
    'cid' => '13e',
    'class_name' => 'ilTestPasswordProtectionGUI',
    'class_path' => './components/ILIAS/Test/classes/class.ilTestPasswordProtectionGUI.php',
    'children' => 
    array (
      0 => 'ilpropertyformgui',
    ),
    'parents' => 
    array (
      0 => 'iltestplayerfixedquestionsetgui',
      1 => 'iltestplayerrandomquestionsetgui',
    ),
  ),
  'iltestpersonalskillsgui' => 
  array (
    'cid' => '13g',
    'class_name' => 'ilTestPersonalSkillsGUI',
    'class_path' => './components/ILIAS/Test/classes/class.ilTestPersonalSkillsGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'iltestskillevaluationgui',
    ),
  ),
  'iltestplayerfixedquestionsetgui' => 
  array (
    'cid' => '13i',
    'class_name' => 'ilTestPlayerFixedQuestionSetGUI',
    'class_path' => './components/ILIAS/Test/classes/class.ilTestPlayerFixedQuestionSetGUI.php',
    'children' => 
    array (
      0 => 'ilassgenfeedbackpagegui',
      1 => 'ilassspecfeedbackpagegui',
      2 => 'ilassquestionhintrequestgui',
      3 => 'ilassquestionpagegui',
      4 => 'iltestsubmissionreviewgui',
      5 => 'iltestpasswordprotectiongui',
      6 => 'iltestansweroptionalquestionsconfirmationgui',
      7 => 'ilconfirmationgui',
    ),
    'parents' => 
    array (
      0 => 'ilobjtestgui',
    ),
  ),
  'iltestplayerrandomquestionsetgui' => 
  array (
    'cid' => '13j',
    'class_name' => 'ilTestPlayerRandomQuestionSetGUI',
    'class_path' => './components/ILIAS/Test/classes/class.ilTestPlayerRandomQuestionSetGUI.php',
    'children' => 
    array (
      0 => 'ilassgenfeedbackpagegui',
      1 => 'ilassspecfeedbackpagegui',
      2 => 'ilassquestionhintrequestgui',
      3 => 'ilassquestionpagegui',
      4 => 'iltestsubmissionreviewgui',
      5 => 'iltestpasswordprotectiongui',
      6 => 'iltestansweroptionalquestionsconfirmationgui',
      7 => 'ilconfirmationgui',
    ),
    'parents' => 
    array (
      0 => 'ilobjtestgui',
    ),
  ),
  'iltestquestionbrowsertablegui' => 
  array (
    'cid' => '13k',
    'class_name' => 'ilTestQuestionBrowserTableGUI',
    'class_path' => './components/ILIAS/Test/classes/tables/class.ilTestQuestionBrowserTableGUI.php',
    'children' => 
    array (
      0 => 'ilformpropertydispatchgui',
    ),
    'parents' => 
    array (
      0 => 'ilobjtestgui',
    ),
  ),
  'iltestrandomquestionsetconfiggui' => 
  array (
    'cid' => '13n',
    'class_name' => 'ilTestRandomQuestionSetConfigGUI',
    'class_path' => './components/ILIAS/Test/classes/class.ilTestRandomQuestionSetConfigGUI.php',
    'children' => 
    array (
      0 => 'iltestrandomquestionsetgeneralconfigformgui',
      1 => 'iltestrandomquestionsetsourcepooldefinitionlisttoolbargui',
      2 => 'iltestrandomquestionsetsourcepooldefinitionlisttablegui',
      3 => 'iltestrandomquestionsetnonavailablepoolstablegui',
      4 => 'ilrepositoryselectorexplorergui',
      5 => 'iltestrandomquestionsetpooldefinitionformgui',
    ),
    'parents' => 
    array (
      0 => 'ilobjtestgui',
    ),
  ),
  'iltestrandomquestionsetgeneralconfigformgui' => 
  array (
    'cid' => '13o',
    'class_name' => 'ilTestRandomQuestionSetGeneralConfigFormGUI',
    'class_path' => './components/ILIAS/Test/classes/forms/class.ilTestRandomQuestionSetGeneralConfigFormGUI.php',
    'children' => 
    array (
      0 => 'ilformpropertydispatchgui',
    ),
    'parents' => 
    array (
      0 => 'iltestrandomquestionsetconfiggui',
    ),
  ),
  'iltestrandomquestionsetnonavailablepoolstablegui' => 
  array (
    'cid' => '13p',
    'class_name' => 'ilTestRandomQuestionSetNonAvailablePoolsTableGUI',
    'class_path' => './components/ILIAS/Test/classes/tables/class.ilTestRandomQuestionSetNonAvailablePoolsTableGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'iltestrandomquestionsetconfiggui',
    ),
  ),
  'iltestrandomquestionsetpooldefinitionformgui' => 
  array (
    'cid' => '13q',
    'class_name' => 'ilTestRandomQuestionSetPoolDefinitionFormGUI',
    'class_path' => './components/ILIAS/Test/classes/forms/class.ilTestRandomQuestionSetPoolDefinitionFormGUI.php',
    'children' => 
    array (
      0 => 'ilformpropertydispatchgui',
    ),
    'parents' => 
    array (
      0 => 'iltestrandomquestionsetconfiggui',
    ),
  ),
  'iltestrandomquestionsetsourcepooldefinitionlisttablegui' => 
  array (
    'cid' => '13r',
    'class_name' => 'ilTestRandomQuestionSetSourcePoolDefinitionListTableGUI',
    'class_path' => './components/ILIAS/Test/classes/tables/class.ilTestRandomQuestionSetSourcePoolDefinitionListTableGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'iltestrandomquestionsetconfiggui',
    ),
  ),
  'iltestrandomquestionsetsourcepooldefinitionlisttoolbargui' => 
  array (
    'cid' => '13s',
    'class_name' => 'ilTestRandomQuestionSetSourcePoolDefinitionListToolbarGUI',
    'class_path' => './components/ILIAS/Test/classes/toolbars/class.ilTestRandomQuestionSetSourcePoolDefinitionListToolbarGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'iltestrandomquestionsetconfiggui',
    ),
  ),
  'iltestresultsgui' => 
  array (
    'cid' => '13t',
    'class_name' => 'ilTestResultsGUI',
    'class_path' => './components/ILIAS/Test/classes/class.ilTestResultsGUI.php',
    'children' => 
    array (
      0 => 'ilparticipantstestresultsgui',
      1 => 'ilmytestresultsgui',
      2 => 'iltestevalobjectiveorientedgui',
      3 => 'ilmytestsolutionsgui',
      4 => 'iltesttoplistgui',
      5 => 'iltestskillevaluationgui',
    ),
    'parents' => 
    array (
      0 => 'ilobjtestgui',
    ),
  ),
  'iltestresultstoolbargui' => 
  array (
    'cid' => '13u',
    'class_name' => 'ilTestResultsToolbarGUI',
    'class_path' => './components/ILIAS/Test/classes/toolbars/class.ilTestResultsToolbarGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjtestgui',
      1 => 'iltestevalobjectiveorientedgui',
      2 => 'iltestevaluationgui',
    ),
  ),
  'iltestscoringbyquestionsgui' => 
  array (
    'cid' => '13v',
    'class_name' => 'ilTestScoringByQuestionsGUI',
    'class_path' => './components/ILIAS/Test/classes/class.ilTestScoringByQuestionsGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjtestgui',
    ),
  ),
  'iltestscoringgui' => 
  array (
    'cid' => '13w',
    'class_name' => 'ilTestScoringGUI',
    'class_path' => './components/ILIAS/Test/classes/class.ilTestScoringGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjtestgui',
    ),
  ),
  'iltestscreengui' => 
  array (
    'cid' => '13x',
    'class_name' => 'ilTestScreenGUI',
    'class_path' => './components/ILIAS/Test/classes/TestScreen/class.ilTestScreenGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjtestgui',
    ),
  ),
  'iltestservicegui' => 
  array (
    'cid' => '13y',
    'class_name' => 'ilTestServiceGUI',
    'class_path' => './components/ILIAS/Test/classes/class.ilTestServiceGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjtestgui',
    ),
  ),
  'iltestsettingschangeconfirmationgui' => 
  array (
    'cid' => '13z',
    'class_name' => 'ilTestSettingsChangeConfirmationGUI',
    'class_path' => './components/ILIAS/Test/classes/confirmations/class.ilTestSettingsChangeConfirmationGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjtestgui',
      1 => 'ilobjtestsettingsmaingui',
    ),
  ),
  'iltestskilladministrationgui' => 
  array (
    'cid' => '141',
    'class_name' => 'ilTestSkillAdministrationGUI',
    'class_path' => './components/ILIAS/Test/classes/class.ilTestSkillAdministrationGUI.php',
    'children' => 
    array (
      0 => 'ilassquestionskillassignmentsgui',
      1 => 'iltestskilllevelthresholdsgui',
    ),
    'parents' => 
    array (
      0 => 'ilobjtestgui',
    ),
  ),
  'iltestskillevaluationgui' => 
  array (
    'cid' => '142',
    'class_name' => 'ilTestSkillEvaluationGUI',
    'class_path' => './components/ILIAS/Test/classes/class.ilTestSkillEvaluationGUI.php',
    'children' => 
    array (
      0 => 'iltestskillevaluationtoolbargui',
      1 => 'iltestpersonalskillsgui',
    ),
    'parents' => 
    array (
      0 => 'iltestresultsgui',
    ),
  ),
  'iltestskillevaluationtoolbargui' => 
  array (
    'cid' => '143',
    'class_name' => 'ilTestSkillEvaluationToolbarGUI',
    'class_path' => './components/ILIAS/Test/classes/toolbars/class.ilTestSkillEvaluationToolbarGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'iltestskillevaluationgui',
    ),
  ),
  'iltestskilllevelthresholdsgui' => 
  array (
    'cid' => '144',
    'class_name' => 'ilTestSkillLevelThresholdsGUI',
    'class_path' => './components/ILIAS/Test/classes/class.ilTestSkillLevelThresholdsGUI.php',
    'children' => 
    array (
      0 => 'iltestskilllevelthresholdstablegui',
    ),
    'parents' => 
    array (
      0 => 'iltestskilladministrationgui',
    ),
  ),
  'iltestskilllevelthresholdstablegui' => 
  array (
    'cid' => '145',
    'class_name' => 'ilTestSkillLevelThresholdsTableGUI',
    'class_path' => './components/ILIAS/Test/classes/tables/class.ilTestSkillLevelThresholdsTableGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'iltestskilllevelthresholdsgui',
    ),
  ),
  'iltestsubmissionreviewgui' => 
  array (
    'cid' => '146',
    'class_name' => 'ilTestSubmissionReviewGUI',
    'class_path' => './components/ILIAS/Test/classes/class.ilTestSubmissionReviewGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'iltestplayerfixedquestionsetgui',
      1 => 'iltestplayerrandomquestionsetgui',
    ),
  ),
  'iltesttoplistgui' => 
  array (
    'cid' => '148',
    'class_name' => 'ilTestToplistGUI',
    'class_path' => './components/ILIAS/Test/classes/class.ilTestToplistGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'iltestresultsgui',
    ),
  ),
  'iltimingoverviewtablegui' => 
  array (
    'cid' => '14f',
    'class_name' => 'ilTimingOverviewTableGUI',
    'class_path' => './components/ILIAS/Test/classes/tables/class.ilTimingOverviewTableGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'iltestparticipantstimeextensiongui',
    ),
  ),
  'iltoolbargui' => 
  array (
    'cid' => '14i',
    'class_name' => 'ilToolbarGUI',
    'class_path' => './Services/UIComponent/Toolbar/classes/class.ilToolbarGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilassquestionhintsgui',
      1 => 'ilassquestionskillassignmentsgui',
      2 => 'ilobjquestionpoolgui',
    ),
  ),
  'iltrmatrixtablegui' => 
  array (
    'cid' => '14k',
    'class_name' => 'ilTrMatrixTableGUI',
    'class_path' => './Services/Tracking/classes/repository_statistics/class.ilTrMatrixTableGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'illplistofobjectsgui',
    ),
  ),
  'iltrobjectuserspropstablegui' => 
  array (
    'cid' => '14l',
    'class_name' => 'ilTrObjectUsersPropsTableGUI',
    'class_path' => './Services/Tracking/classes/repository_statistics/class.ilTrObjectUsersPropsTableGUI.php',
    'children' => 
    array (
      0 => 'ilformpropertydispatchgui',
    ),
    'parents' => 
    array (
      0 => 'illplistofobjectsgui',
    ),
  ),
  'iltrsummarytablegui' => 
  array (
    'cid' => '14m',
    'class_name' => 'ilTrSummaryTableGUI',
    'class_path' => './Services/Tracking/classes/repository_statistics/class.ilTrSummaryTableGUI.php',
    'children' => 
    array (
      0 => 'ilformpropertydispatchgui',
    ),
    'parents' => 
    array (
      0 => 'illplistofobjectsgui',
    ),
  ),
  'iltruserobjectspropstablegui' => 
  array (
    'cid' => '14n',
    'class_name' => 'ilTrUserObjectsPropsTableGUI',
    'class_path' => './Services/Tracking/classes/repository_statistics/class.ilTrUserObjectsPropsTableGUI.php',
    'children' => 
    array (
      0 => 'ilformpropertydispatchgui',
    ),
    'parents' => 
    array (
      0 => 'illplistofobjectsgui',
    ),
  ),
  'iltranslationgui' => 
  array (
    'cid' => '14o',
    'class_name' => 'ilTranslationGUI',
    'class_path' => './components/ILIAS/OrgUnit/classes/Translation/class.ilTranslationGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjorgunitgui',
    ),
  ),
  'iluiasyncdemofileuploadhandlergui' => 
  array (
    'cid' => '14r',
    'class_name' => 'ilUIAsyncDemoFileUploadHandlerGUI',
    'class_path' => './Services/UI/classes/class.ilUIAsyncDemoFileUploadHandlerGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'iluipluginroutergui',
    ),
  ),
  'iluidemofileuploadhandlergui' => 
  array (
    'cid' => '14s',
    'class_name' => 'ilUIDemoFileUploadHandlerGUI',
    'class_path' => './Services/UI/classes/class.ilUIDemoFileUploadHandlerGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'iluipluginroutergui',
    ),
  ),
  'iluimarkdownpreviewgui' => 
  array (
    'cid' => '14u',
    'class_name' => 'ilUIMarkdownPreviewGUI',
    'class_path' => './Services/UI/classes/class.ilUIMarkdownPreviewGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
    ),
  ),
  'iluipluginroutergui' => 
  array (
    'cid' => '14v',
    'class_name' => 'ilUIPluginRouterGUI',
    'class_path' => './Services/UIComponent/classes/class.ilUIPluginRouterGUI.php',
    'children' => 
    array (
      0 => 'ilbuddysystemgui',
      1 => 'iluiasyncdemofileuploadhandlergui',
      2 => 'iluidemofileuploadhandlergui',
    ),
    'parents' => 
    array (
    ),
  ),
  'iluploadlimitsoverviewgui' => 
  array (
    'cid' => '150',
    'class_name' => 'ilUploadLimitsOverviewGUI',
    'class_path' => './Services/FileServices/classes/UploadService/UploadLimits/class.ilUploadLimitsOverviewGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjfileservicesgui',
    ),
  ),
  'iluseractionadmingui' => 
  array (
    'cid' => '152',
    'class_name' => 'ilUserActionAdminGUI',
    'class_path' => './Services/User/classes/Actions/class.ilUserActionAdminGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjawarenessadministrationgui',
      1 => 'ilobjcourseadministrationgui',
      2 => 'ilobjgroupadministrationgui',
    ),
  ),
  'ilusercertificateapigui' => 
  array (
    'cid' => '155',
    'class_name' => 'ilUserCertificateApiGUI',
    'class_path' => './Services/Certificate/classes/API/Download/class.ilUserCertificateApiGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilmstlistcertificatesgui',
    ),
  ),
  'ilusercertificategui' => 
  array (
    'cid' => '156',
    'class_name' => 'ilUserCertificateGUI',
    'class_path' => './Services/Certificate/classes/User/class.ilUserCertificateGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilachievementsgui',
      1 => 'ilmstshowusergui',
    ),
  ),
  'iluserfiltergui' => 
  array (
    'cid' => '15a',
    'class_name' => 'ilUserFilterGUI',
    'class_path' => './Services/Search/classes/class.ilUserFilterGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'illplistofobjectsgui',
    ),
  ),
  'iluserprivacysettingsgui' => 
  array (
    'cid' => '15e',
    'class_name' => 'ilUserPrivacySettingsGUI',
    'class_path' => './Services/User/classes/Settings/class.ilUserPrivacySettingsGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilpersonalprofilegui',
    ),
  ),
  'iluserprofileinfosettingsgui' => 
  array (
    'cid' => '15g',
    'class_name' => 'ilUserProfileInfoSettingsGUI',
    'class_path' => './Services/User/classes/ProfilePrompt/class.ilUserProfileInfoSettingsGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjuserfoldergui',
    ),
  ),
  'iluserstartingpointgui' => 
  array (
    'cid' => '15i',
    'class_name' => 'ilUserStartingPointGUI',
    'class_path' => './Services/User/classes/class.ilUserStartingPointGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjuserfoldergui',
    ),
  ),
  'ilusertablegui' => 
  array (
    'cid' => '15j',
    'class_name' => 'ilUserTableGUI',
    'class_path' => './Services/User/classes/class.ilUserTableGUI.php',
    'children' => 
    array (
      0 => 'ilformpropertydispatchgui',
    ),
    'parents' => 
    array (
      0 => 'ilobjcategorygui',
      1 => 'ilobjemployeetalkgui',
      2 => 'ilobjemployeetalkseriesgui',
      3 => 'ilobjorgunitgui',
      4 => 'ilobjtalktemplateadministrationgui',
      5 => 'ilobjtalktemplategui',
      6 => 'ilobjuserfoldergui',
    ),
  ),
  'ilusersgallerygui' => 
  array (
    'cid' => '15k',
    'class_name' => 'ilUsersGalleryGUI',
    'class_path' => './Services/User/classes/Gallery/class.ilUsersGalleryGUI.php',
    'children' => 
    array (
      0 => 'ilpublicuserprofilegui',
    ),
    'parents' => 
    array (
      0 => 'ilcoursemembershipgui',
      1 => 'ilgroupmembershipgui',
      2 => 'ilcontactgui',
      3 => 'illearningsequencemembershipgui',
      4 => 'ilsessionmembershipgui',
    ),
  ),
  'ilwopiadministrationgui' => 
  array (
    'cid' => '15m',
    'class_name' => 'ilWOPIAdministrationGUI',
    'class_path' => './Services/WOPI/classes/Administration/class.ilWOPIAdministrationGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjexternaltoolssettingsgui',
    ),
  ),
  'ilwopiembeddedapplicationgui' => 
  array (
    'cid' => '15n',
    'class_name' => 'ilWOPIEmbeddedApplicationGUI',
    'class_path' => './Services/WOPI/classes/Embed/class.ilWOPIEmbeddedApplicationGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilfileversionsgui',
      1 => 'ilobjfilegui',
    ),
  ),
  'ilwebdavmountinstructionsuploadgui' => 
  array (
    'cid' => '15t',
    'class_name' => 'ilWebDAVMountInstructionsUploadGUI',
    'class_path' => './Services/WebDAV/classes/mount_instructions/class.ilWebDAVMountInstructionsUploadGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjwebdavgui',
    ),
  ),
  'ilwikihandlergui' => 
  array (
    'cid' => '160',
    'class_name' => 'ilWikiHandlerGUI',
    'class_path' => './components/ILIAS/Wiki/classes/class.ilWikiHandlerGUI.php',
    'children' => 
    array (
      0 => 'ilobjwikigui',
    ),
    'parents' => 
    array (
    ),
  ),
  'ilwikipagegui' => 
  array (
    'cid' => '163',
    'class_name' => 'ilWikiPageGUI',
    'class_path' => './components/ILIAS/Wiki/classes/class.ilWikiPageGUI.php',
    'children' => 
    array (
      0 => 'ilpageeditorgui',
      1 => 'ileditclipboardgui',
      2 => 'ilpublicuserprofilegui',
      3 => 'ilpageobjectgui',
      4 => 'ilnotegui',
      5 => 'ilcommentgui',
      6 => 'ilcommonactiondispatchergui',
      7 => 'ilratinggui',
      8 => 'ilwikistatgui',
      9 => 'ilobjectmetadatagui',
      10 => 'ilpropertyformgui',
    ),
    'parents' => 
    array (
      0 => 'ilobjwikigui',
    ),
  ),
  'ilwikipagetemplategui' => 
  array (
    'cid' => '164',
    'class_name' => 'ilWikiPageTemplateGUI',
    'class_path' => './components/ILIAS/Wiki/classes/class.ilWikiPageTemplateGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjwikigui',
    ),
  ),
  'ilwikistatgui' => 
  array (
    'cid' => '169',
    'class_name' => 'ilWikiStatGUI',
    'class_path' => './components/ILIAS/Wiki/classes/class.ilWikiStatGUI.php',
    'children' => 
    array (
    ),
    'parents' => 
    array (
      0 => 'ilobjwikigui',
      1 => 'ilwikipagegui',
    ),
  ),
  'ilworkspaceaccessgui' => 
  array (
    'cid' => '16a',
    'class_name' => 'ilWorkspaceAccessGUI',
    'class_path' => './Services/PersonalWorkspace/classes/class.ilWorkspaceAccessGUI.php',
    'children' => 
    array (
      0 => 'ilmailsearchcoursesgui',
      1 => 'ilmailsearchgroupsgui',
      2 => 'ilmailsearchgui',
      3 => 'ilpublicuserprofilegui',
      4 => 'ilsingleusersharegui',
    ),
    'parents' => 
    array (
      0 => 'ilobjbloggui',
      1 => 'ilobjcourseverificationgui',
      2 => 'ilobjexerciseverificationgui',
      3 => 'ilobjfilegui',
      4 => 'ilobjlinkresourcegui',
      5 => 'ilobjportfoliogui',
      6 => 'ilobjscormverificationgui',
      7 => 'ilobjtestverificationgui',
    ),
  ),
);