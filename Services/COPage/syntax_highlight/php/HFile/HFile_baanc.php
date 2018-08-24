<?php
$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_baanc extends HFile{
   function HFile_baanc(){
     $this->HFile();	
/*************************************/
// Beautifier Highlighting Configuration File 
// Baan C
/*************************************/
// Flags

$this->nocase            	= "0";
$this->notrim            	= "0";
$this->perl              	= "0";

// Colours

$this->colours        	= array("blue", "purple", "gray", "brown", "blue", "purple");
$this->quotecolour       	= "blue";
$this->blockcommentcolour	= "green";
$this->linecommentcolour 	= "green";

// Indent Strings

$this->indent            	= array();
$this->unindent          	= array();

// String characters and delimiters

$this->stringchars       	= array("\"", "'");
$this->delimiters        	= array("~", "!", "@", "%", "^", "&", "-", "+", "=", "|", "\\", "/", "{", "}", "[", "]", ";", "\"", "'", "<", ">", " ", ",", "	", "?");
$this->escchar           	= "";

// Comment settings

$this->linecommenton     	= array("|");
$this->blockcommenton    	= array("DllUsage");
$this->blockcommentoff   	= array("EndDllUsage");

// Keywords (keyword mapping to colour number)

$this->keywords          	= array(
			"#define" => "1", 
			"#ident" => "1", 
			"#include" => "1", 
			"#pragma" => "1", 
			"ALL_ENUMS_EXCEPT" => "1", 
			"APPL.READ" => "1", 
			"APPL.WRITE" => "1", 
			"APPL.EXCL" => "1", 
			"APPL.WIDE" => "1", 
			"APPL.WAIT" => "1", 
			"DAL_NEW" => "1", 
			"DAL_UPDATE" => "1", 
			"DAL_DESTROY" => "1", 
			"DALHOOKERROR" => "1", 
			"DAL_GET_FIRST" => "1", 
			"DAL_GET_LAST" => "1", 
			"DAL_GET_NEXT" => "1", 
			"DAL_GET_PREV" => "1", 
			"DAL_GET_FIND" => "1", 
			"DAL_GET_CURR" => "1", 
			"EXTEND_APPEND" => "1", 
			"abort" => "1", 
			"abort.io" => "1", 
			"abort.program" => "1", 
			"abort.transaction" => "1", 
			"abs" => "1", 
			"acos" => "1", 
			"act.and.sleep" => "1", 
			"activate" => "1", 
			"activate.search" => "1", 
			"add.set" => "1", 
			"add.view.field" => "1", 
			"alloc.mem" => "1", 
			"appl.delete" => "1", 
			"appl.get.user" => "1", 
			"appl.modify" => "1", 
			"appl.set" => "1", 
			"argc" => "1", 
			"argv$" => "1", 
			"array.info" => "1", 
			"asc" => "1", 
			"asin" => "1", 
			"ask.enum" => "1", 
			"at.base" => "1", 
			"ask.helpinfo" => "1", 
			"atan" => "1", 
			"aud_close_audit" => "1", 
			"aud_get_audit_dd" => "1", 
			"aud_get_col_info" => "1", 
			"aud_get_fld_values" => "1", 
			"aud_get_hdr_size" => "1", 
			"aud_get_info_seq_hdr_size" => "1", 
			"aud_open_audit" => "1", 
			"aud_read_audit_rec" => "1", 
			"aud_read_info_hdr" => "1", 
			"aud_read_info_seq_hdr" => "1", 
			"aud_read_next_audit_rec" => "1", 
			"aud_read_next_tran" => "1", 
			"aud_read_seq_hdr" => "1", 
			"aud_read_tran" => "1", 
			"aud_select_host" => "1", 
			"aud_update_infofile" => "1", 
			"aud_write_info_hdr" => "1", 
			"aud_write_seq_hdr" => "1", 
			"aux.close" => "1", 
			"aux.open" => "1", 
			"aux.print" => "1", 
			"avg" => "1", 
			"based" => "1", 
			"bc$" => "1", 
			"bell$" => "1", 
			"bg$" => "1", 
			"bit.and" => "1", 
			"bit.exor" => "1", 
			"bit.in" => "1", 
			"bit.inv" => "1", 
			"bit.or" => "1", 
			"bit.shiftl" => "1", 
			"bit.shiftr" => "1", 
			"blank.all" => "1", 
			"blue.component" => "1", 
			"bms" => "1", 
			"bms.add.mask" => "1", 
			"bms.delete.mask" => "1", 
			"bms.receive.buffer" => "1", 
			"bms.receive$" => "1", 
			"bms.send" => "1", 
			"box" => "1", 
			"brp.close" => "1", 
			"brp.open" => "1", 
			"brp.open.language" => "1", 
			"brp.ready" => "1", 
			"bs$" => "1", 
			"bse.dir$" => "1", 
			"bse.release$" => "1", 
			"bse.tmp.dir$" => "1", 
			"bshell.pid" => "1", 
			"cf$" => "1", 
			"calculator" => "1", 
			"calendar" => "1", 
			"change.field.label" => "1", 
			"change.mwindow" => "1", 
			"change.object" => "1", 
			"change.progress.indicator" => "1", 
			"change.order" => "1", 
			"change.sub.object" => "1", 
			"change.window" => "1", 
			"changed" => "1", 
			"check.all.input" => "1", 
			"choose.time.zone.from.list" => "1", 
			"chm.axis.in" => "1", 
			"chm.axis.out" => "1", 
			"chm.chartman" => "1", 
			"chm.data2domain.in" => "1", 
			"chm.data.in" => "1", 
			"chm.delete.data2domains" => "1", 
			"chm.delete.data" => "1", 
			"chm.delete.footnotes" => "1", 
			"chm.delete.projections" => "1", 
			"chm.delete.sets" => "1", 
			"chm.disconnect" => "1", 
			"chm.domain.in" => "1", 
			"chm.domain.out" => "1", 
			"chm.draw" => "1", 
			"chm.first.data2domain.out" => "1", 
			"chm.first.data.out" => "1", 
			"chm.first.footnote.out" => "1", 
			"chm.first.projection.out" => "1", 
			"chm.first.set.out" => "1", 
			"chm.footnote.in" => "1", 
			"chm.get.request" => "1", 
			"chm.next.data2domain.out" => "1", 
			"chm.next.data.out" => "1", 
			"chm.next.footnote.out" => "1", 
			"chm.next.projection.out" => "1", 
			"chm.next.set.out" => "1", 
			"chm.new" => "1", 
			"chm.open" => "1", 
			"chm.projection.in" => "1", 
			"chm.remove" => "1", 
			"chm.scale.axis" => "1", 
			"chm.select" => "1", 
			"chm.set.in" => "1", 
			"chm.set.option" => "1", 
			"chm.set.timer" => "1", 
			"chm.title.in" => "1", 
			"chm.title.out" => "1", 
			"choice.again" => "1", 
			"choice.report" => "1", 
			"chr$" => "1", 
			"cl.screen" => "1", 
			"clean.mess" => "1", 
			"close.message" => "1", 
			"cmd.options" => "1", 
			"cmd.whats.this" => "1", 
			"cmp.mem" => "1", 
			"commit.transaction" => "1", 
			"compnr.check" => "1", 
			"compress.pixmap" => "1", 
			"concat$" => "1", 
			"cont.process" => "1", 
			"copy.mem" => "1", 
			"cos" => "1", 
			"cosh" => "1", 
			"count" => "1", 
			"cp$" => "1", 
			"cr$" => "1", 
			"create.job" => "1", 
			"create.extra.toolbar" => "1", 
			"create.mwindow" => "1", 
			"create.node" => "1", 
			"create.object" => "1", 
			"create.progress.indicator" => "1", 
			"create.sub.object" => "1", 
			"create.sub.object.by.id" => "1", 
			"creat.tmp.file$" => "1", 
			"create.tree.button" => "1", 
			"create.tree" => "1", 
			"cs$" => "1", 
			"current.display" => "1", 
			"current.mwindow" => "1", 
			"current.window" => "1", 
			"d.expr" => "1", 
			"dal.count.error.messages" => "1", 
			"dal.get.error.message" => "1", 
			"dal.get.first.error.message" => "1", 
			"dal.reset.error.messages" => "1", 
			"dal.set.error.message" => "1", 
			"dal.destroy" => "1", 
			"dal.get.property.flag" => "1", 
			"dal.new" => "1", 
			"dal.set.property" => "1", 
			"dal.start.business.method" => "1", 
			"dal.update" => "1", 
			"data.input" => "1", 
			"date.num" => "1", 
			"date.time.utc" => "1", 
			"date.to.date" => "1", 
			"date.to.inputstr$" => "1", 
			"date.to.num" => "1", 
			"date.to.utc" => "1", 
			"db.bind" => "1", 
			"db.change.order" => "1", 
			"db.check.restricted" => "1", 
			"db.check.row.changed" => "1", 
			"db.check.row.domains" => "1", 
			"db.clear.table" => "1", 
			"db.columns.to.record" => "1", 
			"db.create.table" => "1", 
			"db.curr" => "1", 
			"db.delete" => "1", 
			"db.drop.table" => "1", 
			"db.eq" => "1", 
			"db.error" => "1", 
			"db.error.message" => "1", 
			"db.first" => "1", 
			"db.ge" => "1", 
			"db.gt" => "1", 
			"db.indexinfo" => "1", 
			"db.insert" => "1", 
			"db.last" => "1", 
			"db.le" => "1", 
			"db.lock.table" => "1", 
			"db.lt" => "1", 
			"db.next" => "1", 
			"db.nr.indices" => "1", 
			"db.nr.rows" => "1", 
			"db.permission" => "1", 
			"db.prev" => "1", 
			"db.record.to.columns" => "1", 
			"db.ref.handle.mode" => "1", 
			"db.retry.point" => "1", 
			"db.retry.hit" => "1", 
			"db.row.length" => "1", 
			"db.set.to.default" => "1", 
			"db.unbind" => "1", 
			"db.update" => "1", 
			"decompress.pixmap" => "1", 
			"def.find" => "1", 
			"del.window" => "1", 
			"delch$" => "1", 
			"deleteln$" => "1", 
			"destroy.mwindow" => "1", 
			"destroy.object" => "1", 
			"destroy.progress.indicator" => "1", 
			"destroy.sub.object" => "1", 
			"destroy.tree" => "1", 
			"dir.close" => "1", 
			"dir.entry" => "1", 
			"dir.open" => "1", 
			"dir.open.tree" => "1", 
			"dir.rewind" => "1", 
			"disable.commands" => "1", 
			"disable.fields" => "1", 
			"display" => "1", 
			"display.all" => "1", 
			"display.curr.occ" => "1", 
			"display.fld" => "1", 
			"display.occ" => "1", 
			"display.set" => "1", 
			"display.total.fields" => "1", 
			"dll" => "1", 
			"do.all.occ" => "1", 
			"do.occ" => "1", 
			"do.occ.without.update" => "1", 
			"double.cmp" => "1", 
			"dte$" => "1", 
			"dump.window" => "1", 
			"dupl.occur" => "1", 
			"edit$" => "1", 
			"el$" => "1", 
			"end" => "1", 
			"end(4GL)" => "1", 
			"enable.commands" => "1", 
			"enable.fields" => "1", 
			"end.program" => "1", 
			"enum.descr$" => "1", 
			"es$" => "1", 
			"etol" => "1", 
			"exec_dll_function" => "1", 
			"exec_function" => "1", 
			"execute" => "1", 
			"exit" => "1", 
			"exp" => "1", 
			"export" => "1", 
			"expr.compile" => "1", 
			"expr.free" => "1", 
			"ff$" => "1", 
			"fg$" => "1", 
			"field.*:" => "1", 
			"file.chmod" => "1", 
			"file.chown" => "1", 
			"file.cp" => "1", 
			"file.mv" => "1", 
			"file.mv.across.hosts" => "1", 
			"file.rm" => "1", 
			"file.stat" => "1", 
			"find.data" => "1", 
			"first.frm" => "1", 
			"first.set" => "1", 
			"first.view" => "1", 
			"first.window" => "1", 
			"form.tab.change" => "1", 
			"form.text$" => "1", 
			"format.round" => "1", 
			"free.mem" => "1", 
			"fs$" => "1", 
			"fstat.info" => "1", 
			"get.arg.type" => "1", 
			"get.argc" => "1", 
			"get.col" => "1", 
			"get.company" => "1", 
			"get.compnr" => "1", 
			"get.cp" => "1", 
			"get.defaults" => "1", 
			"get.display.data" => "1", 
			"get.double.arg" => "1", 
			"get.field.label" => "1", 
			"get.indexed.var" => "1", 
			"get.long.arg" => "1", 
			"get.mwindow.attrs" => "1", 
			"get.mwindow.mode" => "1", 
			"get.mwindow.size" => "1", 
			"get.object" => "1", 
			"get.pgrp" => "1", 
			"get.pixmap.info" => "1", 
			"get.resource$" => "1", 
			"get.row" => "1", 
			"get.sub.object" => "1", 
			"get.screen.defaults" => "1", 
			"get.string.arg" => "1", 
			"get.time.zone" => "1", 
			"get.tree.default" => "1", 
			"get.tree.node.dpress" => "1", 
			"get.tree.node.press" => "1", 
			"get.tree.push.button" => "1", 
			"get.var" => "1", 
			"get.window.attrs" => "1", 
			"get_function" => "1", 
			"getcwd" => "1", 
			"getenv$" => "1", 
			"global.copy" => "1", 
			"global.delete" => "1", 
			"grab.mwindow" => "1", 
			"green.component" => "1", 
			"group.exists" => "1", 
			"help.index" => "1", 
			"hostname$" => "1", 
			"import" => "1", 
			"input" => "1", 
			"input.again" => "1", 
			"input.to.utc" => "1", 
			"inputstr.to.date" => "1", 
			"inputstr.to.utc" => "1", 
			"inputfield.invisible" => "1", 
			"inputfield.password" => "1", 
			"inputfield.visible" => "1", 
			"insch$" => "1", 
			"insertln$" => "1", 
			"int" => "1", 
			"interrupt" => "1", 
			"is.field.invisible" => "1", 
			"is.option.on" => "1", 
			"isdigit" => "1", 
			"isspace" => "1", 
			"keyin$" => "1", 
			"kill" => "1", 
			"kill.pgrp" => "1", 
			"kill.timer" => "1", 
			"l.expr" => "1", 
			"last.window" => "1", 
			"len" => "1", 
			"len.in.bytes" => "1", 
			"lf$" => "1", 
			"last.frm" => "1", 
			"last.set" => "1", 
			"last.view" => "1", 
			"load.byte" => "1", 
			"load_dll" => "1", 
			"load.double" => "1", 
			"load.float" => "1", 
			"load.long" => "1", 
			"load.short" => "1", 
			"local.to.utc" => "1", 
			"log" => "1", 
			"log10" => "1", 
			"lower.object" => "1", 
			"lpow" => "1", 
			"ltoe" => "1", 
			"lval" => "1", 
			"make.current" => "1", 
			"map.object" => "1", 
			"map.window" => "1", 
			"mark.delete" => "1", 
			"mark.handler" => "1", 
			"mark.occ" => "1", 
			"mark.occur" => "1", 
			"max" => "1", 
			"mb.cast$" => "1", 
			"mb.cast.to.str$" => "1", 
			"mb.char" => "1", 
			"mb.char.info" => "1", 
			"mb.display" => "1", 
			"mb.export$" => "1", 
			"mb.ext.clean$" => "1", 
			"mb.hasbidi" => "1", 
			"mb.import$" => "1", 
			"mb.isbidi" => "1", 
			"mb.isbidi.language" => "1", 
			"mb.kb.lang" => "1", 
			"mb.locale.info" => "1", 
			"mb.localename$" => "1", 
			"mb.long.to.str$" => "1", 
			"mb.nsets" => "1", 
			"mb.rev$" => "1", 
			"mb.scrpos" => "1", 
			"mb.set.info" => "1", 
			"mb.strpos" => "1", 
			"mb.tss.clean$" => "1", 
			"mb.type" => "1", 
			"mb.width" => "1", 
			"mess" => "1", 
			"message" => "1", 
			"min" => "1", 
			"mkdir" => "1", 
			"modify.set" => "1", 
			"move.window" => "1", 
			"mtime" => "1", 
			"next.frm" => "1", 
			"next.set" => "1", 
			"next.view" => "1", 
			"new.window" => "1", 
			"next.event" => "1", 
			"no.scroll" => "1", 
			"not.curr" => "1", 
			"not.fixed" => "1", 
			"num.to.date" => "1", 
			"num.to.date$" => "1", 
			"num.to.week" => "1", 
			"off.change.check" => "1", 
			"on.change.check" => "1", 
			"on.main.table" => "1", 
			"on.old.occ" => "1", 
			"open.message" => "1", 
			"ostype" => "1", 
			"parse_and_exec_function" => "1", 
			"pathname" => "1", 
			"pc$" => "1", 
			"pcm.activate.session" => "1", 
			"pcm.change.object" => "1", 
			"pcm.change" => "1", 
			"pcm.create.object" => "1", 
			"pcm.create" => "1", 
			"pcm.destroy.object" => "1", 
			"pcm.destroy" => "1", 
			"pcm.get.data" => "1", 
			"pcm.lock" => "1", 
			"pcm.refresh" => "1", 
			"pcm.send.event" => "1", 
			"peek.event" => "1", 
			"pending.events" => "1", 
			"pf$" => "1", 
			"pipe.clearerr" => "1", 
			"pipe.close" => "1", 
			"pipe.eof" => "1", 
			"pipe.error" => "1", 
			"pipe.flush" => "1", 
			"pipe.gets" => "1", 
			"pipe.open" => "1", 
			"pipe.puts" => "1", 
			"pipe.read" => "1", 
			"pipe.write" => "1", 
			"pos" => "1", 
			"pow" => "1", 
			"prev.frm" => "1", 
			"prev.set" => "1", 
			"prev.view" => "1", 
			"print.const" => "1", 
			"print.data" => "1", 
			"pstat" => "1", 
			"put.double.arg" => "1", 
			"put.indexed.var" => "1", 
			"put.long.arg" => "1", 
			"put.string.arg" => "1", 
			"put.var" => "1", 
			"qss.search" => "1", 
			"qss.sort" => "1", 
			"query.object" => "1", 
			"raise.object" => "1", 
			"random" => "1", 
			"rdi.audit.hosts" => "1", 
			"rdi.column" => "1", 
			"rdi.column.combined" => "1", 
			"rdi.date.input.format$" => "1", 
			"rdi.domain.byte" => "1", 
			"rdi.domain.combined" => "1", 
			"rdi.domain.date" => "1", 
			"rdi.domain.double" => "1", 
			"rdi.domain.enum" => "1", 
			"rdi.domain.enum.value" => "1", 
			"rdi.domain.float" => "1", 
			"rdi.domain.integer" => "1", 
			"rdi.domain.long" => "1", 
			"rdi.domain.mail" => "1", 
			"rdi.domain.set" => "1", 
			"rdi.domain.string" => "1", 
			"rdi.domain.set.value" => "1", 
			"rdi.domain.text" => "1", 
			"rdi.domain" => "1", 
			"rdi.first.day.of.week" => "1", 
			"rdi.index" => "1", 
			"rdi.reference" => "1", 
			"rdi.table" => "1", 
			"rdi.table.column" => "1", 
			"reactivate" => "1", 
			"receive.bucket$" => "1", 
			"recv.message" => "1", 
			"red.component" => "1", 
			"refresh" => "1", 
			"refresh.curr.occ" => "1", 
			"remove.mark" => "1", 
			"resize.window" => "1", 
			"restore.rcd.main" => "1", 
			"rgb" => "1", 
			"rm.dir" => "1", 
			"rnd.d" => "1", 
			"rnd.i" => "1", 
			"rnd.init" => "1", 
			"round" => "1", 
			"recover.set" => "1", 
			"resize.frm" => "1", 
			"restart.input" => "1", 
			"rotate.curr" => "1", 
			"run.job" => "1", 
			"rpos" => "1", 
			"rprt_close" => "1", 
			"rprt_open" => "1", 
			"rprt_send" => "1", 
			"rsc.boolean" => "1", 
			"rsc.double" => "1", 
			"rsc.enum" => "1", 
			"rsc.font.spec" => "1", 
			"rsc.get" => "1", 
			"rsc.long" => "1", 
			"rsc.put" => "1", 
			"rsc.reload" => "1", 
			"rsc.setboolean" => "1", 
			"rsc.setdouble" => "1", 
			"rsc.setenum" => "1", 
			"rsc.setlong" => "1", 
			"rsc.setstring" => "1", 
			"rsc.string" => "1", 
			"run.baan.prog" => "1", 
			"run.prog" => "1", 
			"s.expr" => "1", 
			"save.defaults" => "1", 
			"scroll" => "1", 
			"select.event.input" => "1", 
			"send.bucket" => "1", 
			"send.event" => "1", 
			"send.message" => "1", 
			"send.wait" => "1", 
			"seq.clearerr" => "1", 
			"seq.close" => "1", 
			"seq.eof" => "1", 
			"seq.error" => "1", 
			"seq.flush" => "1", 
			"seq.getc$" => "1", 
			"seq.gets" => "1", 
			"seq.is.locked" => "1", 
			"seq.lock" => "1", 
			"seq.open" => "1", 
			"seq.putc$" => "1", 
			"seq.puts" => "1", 
			"seq.read" => "1", 
			"seq.rewind" => "1", 
			"seq.seek" => "1", 
			"seq.skip" => "1", 
			"seq.tell" => "1", 
			"seq.ungetc$" => "1", 
			"seq.unlink" => "1", 
			"seq.unlock" => "1", 
			"seq.write" => "1", 
			"session" => "1", 
			"set.alarm" => "1", 
			"set.bg.color" => "1", 
			"set.bitset.values" => "1", 
			"set.currencies" => "1", 
			"set.enum.values" => "1", 
			"set.enum.values.for.field" => "1", 
			"set.fg.color" => "1", 
			"set.fields.default" => "1", 
			"set.fmax" => "1", 
			"set.fmin" => "1", 
			"set.focus" => "1", 
			"set.input.error" => "1", 
			"set.limits.off" => "1", 
			"set.max" => "1", 
			"set.mem" => "1", 
			"set.min" => "1", 
			"set.mwindow.mode" => "1", 
			"set.mwindow.size" => "1", 
			"set.mwindow.title" => "1", 
			"set.node.class.color" => "1", 
			"set.node.class" => "1", 
			"set.pgrp" => "1", 
			"set.sensitive" => "1", 
			"set.strip.mode" => "1", 
			"set.symbol.strip.mode" => "1", 
			"set.synchronized.dialog" => "1", 
			"set.timer" => "1", 
			"set.time.zone" => "1", 
			"set.tree.background" => "1", 
			"set.tree.font" => "1", 
			"set.tree.foreground" => "1", 
			"set.tree.linewidth" => "1", 
			"set.tree.name" => "1", 
			"set.transaction.readonly" => "1", 
			"sf$" => "1", 
			"shell" => "1", 
			"shiftc$" => "1", 
			"shiftl$" => "1", 
			"shiftr$" => "1", 
			"signal" => "1", 
			"sin" => "1", 
			"sinh" => "1", 
			"skip.io" => "1", 
			"sleep" => "1", 
			"spool.buf" => "1", 
			"spool.close" => "1", 
			"spool.line" => "1", 
			"spool.open" => "1", 
			"sprintf$" => "1", 
			"sql.break" => "1", 
			"sql.close" => "1", 
			"sql.exec" => "1", 
			"sql.fetch" => "1", 
			"sql.parse" => "1", 
			"sql.select.bind" => "1", 
			"sql.where.bind" => "1", 
			"sqrt" => "1", 
			"srand" => "1", 
			"start.session" => "1", 
			"stat.info" => "1", 
			"start.chart" => "1", 
			"start.query" => "1", 
			"start.set" => "1", 
			"start.synchronized.child" => "1", 
			"start.synchronized.child.with" => "1", 
			"status.del" => "1", 
			"status.field" => "1", 
			"status.mess" => "1", 
			"status.off" => "1", 
			"status.on" => "1", 
			"stop" => "1", 
			"stop(4GL)" => "1", 
			"stop.synchronized.child" => "1", 
			"store.byte" => "1", 
			"store.double" => "1", 
			"store.float" => "1", 
			"store.long" => "1", 
			"store.occ.max" => "1", 
			"store.occ.min" => "1", 
			"store.short" => "1", 
			"stp.reset.value" => "1", 
			"str$" => "1", 
			"string.scan" => "1", 
			"string.set$" => "1", 
			"strip$" => "1", 
			"sub.window" => "1", 
			"sum" => "1", 
			"suspend" => "1", 
			"switch.to.company" => "1", 
			"switch.to.process" => "1", 
			"synchronize.with.child" => "1", 
			"tab$" => "1", 
			"table.round" => "1", 
			"tan" => "1", 
			"tanh" => "1", 
			"text.copy" => "1", 
			"text.copy.language" => "1", 
			"text.copy.between.companies" => "1", 
			"text.defaults" => "1", 
			"text.delete" => "1", 
			"text.edit" => "1", 
			"text.manager" => "1", 
			"text.present.in.language" => "1", 
			"text.read" => "1", 
			"text.rewrite" => "1", 
			"text.to.buf" => "1", 
			"text.window" => "1", 
			"text.write" => "1", 
			"time.num" => "1", 
			"times.off" => "1", 
			"times.on" => "1", 
			"timezone.exists" => "1", 
			"to.field" => "1", 
			"to.form" => "1", 
			"to.group" => "1", 
			"to.key" => "1", 
			"tolower$" => "1", 
			"toupper$" => "1", 
			"tt.align.according.domain" => "1", 
			"tt.bobject.desc" => "1", 
			"tt.chm.appl.desc" => "1", 
			"tt.chm.application" => "1", 
			"tt.chm.chart" => "1", 
			"tt.chm.charttype" => "1", 
			"tt.company" => "1", 
			"tt.currency" => "1", 
			"tt.device" => "1", 
			"tt.field.desc" => "1", 
			"tt.index.desc" => "1", 
			"tt.language" => "1", 
			"tt.library" => "1", 
			"tt.menu.desc" => "1", 
			"tt.menu.present" => "1", 
			"tt.report.desc" => "1", 
			"tt.reportgroup.exists" => "1", 
			"tt.session.desc" => "1", 
			"tt.session.permission" => "1", 
			"tt.session.present" => "1", 
			"tt.short.field.desc" => "1", 
			"tt.table.desc" => "1", 
			"tt.user" => "1", 
			"ttyname" => "1", 
			"ttyname$" => "1", 
			"unmap.object" => "1", 
			"unmap.window" => "1", 
			"unset.focus" => "1", 
			"update.db" => "1", 
			"update.object" => "1", 
			"update.occ" => "1", 
			"used" => "1", 
			"user.0" => "1", 
			"user.1" => "1", 
			"user.2" => "1", 
			"user.3" => "1", 
			"user.4" => "1", 
			"user.5" => "1", 
			"user.6" => "1", 
			"user.7" => "1", 
			"user.8" => "1", 
			"user.9" => "1", 
			"utc.num" => "1", 
			"utc.to.date" => "1", 
			"utc.to.input" => "1", 
			"utc.to.input$" => "1", 
			"utc.to.local" => "1", 
			"utc.to.week" => "1", 
			"val" => "1", 
			"view.tree" => "1", 
			"vsprintf$" => "1", 
			"wait" => "1", 
			"wait.and.activate" => "1", 
			"wait.for.switch" => "1", 
			"week.to.num" => "1", 
			"week.to.utc" => "1", 
			"with.object.set.do" => "1", 
			"with.old.object.values.do" => "1", 
			"wrebuild" => "1", 
			"zoom" => "1", 
			"zoom.to$" => "1", 
			"actual.occ" => "2", 
			"attr.adju" => "2", 
			"attr.bitset.mask" => "2", 
			"attr.changed" => "2", 
			"attr.conv" => "2", 
			"attr.currency$" => "2", 
			"attr.currkey" => "2", 
			"attr.dbase" => "2", 
			"attr.dbmaxlen" => "2", 
			"attr.deflt$" => "2", 
			"attr.descr$" => "2", 
			"attr.diga" => "2", 
			"attr.digv" => "2", 
			"attr.divf" => "2", 
			"attr.domain$" => "2", 
			"attr.domm$" => "2", 
			"attr.dorp" => "2", 
			"attr.echo" => "2", 
			"attr.element" => "2", 
			"attr.enum.mask$" => "2", 
			"attr.format.addition$" => "2", 
			"attr.helpfile$" => "2", 
			"attr.ille$" => "2", 
			"attr.imax" => "2", 
			"attr.inpfld" => "2", 
			"attr.input" => "2", 
			"attr.lega$" => "2", 
			"attr.mandatory" => "2", 
			"attr.maxlen" => "2", 
			"attr.message$" => "2", 
			"attr.minlen" => "2", 
			"attr.multioccur" => "2", 
			"attr.nowait$" => "2", 
			"attr.oformat$" => "2", 
			"attr.permission" => "2", 
			"attr.previous$" => "2", 
			"attr.rang" => "2", 
			"attr.rang$" => "2", 
			"attr.reallen" => "2", 
			"attr.refpath" => "2", 
			"attr.rndm" => "2", 
			"attr.rotate" => "2", 
			"attr.sttp" => "2", 
			"attr.textfield$" => "2", 
			"attr.textkw1$" => "2", 
			"attr.textkw2$" => "2", 
			"attr.textkw3$" => "2", 
			"attr.textkw4$" => "2", 
			"attr.textlang$" => "2", 
			"attr.textmaxlines" => "2", 
			"attr.textmode" => "2", 
			"attr.textopt$" => "2", 
			"attr.textstart" => "2", 
			"attr.textzoomsession$" => "2", 
			"attr.type" => "2", 
			"attr.zoomcode" => "2", 
			"attr.zoomreturn$" => "2", 
			"attr.zoomsession$" => "2", 
			"auto.nextform" => "2", 
			"background" => "2", 
			"before.update.check" => "2", 
			"breakview" => "2", 
			"chartgrp" => "2", 
			"chm.name" => "2", 
			"chm.owner" => "2", 
			"chm.title" => "2", 
			"chm.user" => "2", 
			"choice" => "2", 
			"curr.key" => "2", 
			"curr.pacc$" => "2", 
			"date" => "2", 
			"date$" => "2", 
			"e" => "2", 
			"error.bypass" => "2", 
			"exit.val$" => "2", 
			"fattr.currfld$" => "2", 
			"fattr.descr$" => "2", 
			"fattr.ftype" => "2", 
			"fattr.helpfile$" => "2", 
			"fattr.horizontal" => "2", 
			"fattr.init" => "2", 
			"fattr.nextfld$" => "2", 
			"fattr.nrtabs" => "2", 
			"fattr.occurnr" => "2", 
			"fattr.prevfld$" => "2", 
			"fattr.row" => "2", 
			"fattr.scrollbar" => "2", 
			"fattr.seqno" => "2", 
			"fattr.step" => "2", 
			"fattr.toplines" => "2", 
			"fattr.total.line" => "2", 
			"fattr.vdate$" => "2", 
			"fattr.version$" => "2", 
			"fattr.width" => "2", 
			"filename$" => "2", 
			"filled.occ" => "2", 
			"firstweek$" => "2", 
			"form.curr" => "2", 
			"form.next" => "2", 
			"form.prev" => "2", 
			"free$" => "2", 
			"graphical.mode" => "2", 
			"ignore.first.event" => "2", 
			"in.ret" => "2", 
			"initial.resize" => "2", 
			"job.device" => "2", 
			"job.device.requested" => "2", 
			"job.process" => "2", 
			"job.report" => "2", 
			"job.skip.date.question" => "2", 
			"language$" => "2", 
			"logname$" => "2", 
			"lattr.autobefores" => "2", 
			"lattr.autoreset" => "2", 
			"lattr.break" => "2", 
			"lattr.enddata" => "2", 
			"lattr.header" => "2", 
			"lattr.language$" => "2", 
			"lattr.lineno" => "2", 
			"lattr.multicol" => "2", 
			"lattr.multicol.count" => "2", 
			"lattr.multicol.repeat" => "2", 
			"lattr.pageno" => "2", 
			"lattr.print" => "2", 
			"lattr.prline" => "2", 
			"lattr.recordtimes" => "2", 
			"lattr.textexpand" => "2", 
			"lattr.textlang$" => "2", 
			"lattr.textline" => "2", 
			"lattr.textlineno" => "2", 
			"lattr.textlines.max" => "2", 
			"lattr.textlines.min" => "2", 
			"main.table$" => "2", 
			"mark.status" => "2", 
			"mark.table()" => "2", 
			"marked" => "2", 
			"max.formtabs" => "2", 
			"maxdouble" => "2", 
			"modify.prim.key" => "2", 
			"number.forms" => "2", 
			"parent" => "2", 
			"pid" => "2", 
			"previous.choice" => "2", 
			"procesinfo$" => "2", 
			"prog.name$" => "2", 
			"query.extend.select" => "2", 
			"query.extend.select.in.zoom" => "2", 
			"query.extend.from" => "2", 
			"query.extend.from.in.zoom" => "2", 
			"query.extend.where" => "2", 
			"query.extend.where.in.zoom" => "2", 
			"query.extension" => "2", 
			"reportgrp" => "2", 
			"reportno" => "2", 
			"sattr.combined" => "2", 
			"stp.abort.error" => "2", 
			"stp.check.input.error" => "2", 
			"stp.skip.error" => "2", 
			"term$" => "2", 
			"time" => "2", 
			"update.status" => "2", 
			"user.exists" => "2", 
			"user.type$" => "2", 
			"zoomfield$" => "2", 
			"zoomreturn$" => "2", 
			"and" => "3", 
			"as" => "3", 
			"at" => "3", 
			"base" => "3", 
			"break" => "3", 
			"bset" => "3", 
			"by" => "3", 
			"call" => "3", 
			"case" => "3", 
			"common" => "3", 
			"const" => "3", 
			"continue" => "3", 
			"default:" => "3", 
			"dim" => "3", 
			"domain" => "3", 
			"double" => "3", 
			"else" => "3", 
			"empty" => "3", 
			"endcase" => "3", 
			"endfor" => "3", 
			"endif" => "3", 
			"endwhile" => "3", 
			"endselect" => "3", 
			"eq" => "3", 
			"extern" => "3", 
			"false" => "3", 
			"fixed" => "3", 
			"for" => "3", 
			"from" => "3", 
			"function" => "3", 
			"ge" => "3", 
			"global" => "3", 
			"goto" => "3", 
			"group" => "3", 
			"gt" => "3", 
			"if" => "3", 
			"in" => "3", 
			"le" => "3", 
			"long" => "3", 
			"lt" => "3", 
			"mb" => "3", 
			"multibyte" => "3", 
			"ne" => "3", 
			"not" => "3", 
			"on" => "3", 
			"or" => "3", 
			"order" => "3", 
			"print" => "3", 
			"prompt" => "3", 
			"ref" => "3", 
			"reference" => "3", 
			"refers" => "3", 
			"repeat" => "3", 
			"return" => "3", 
			"rows" => "3", 
			"select" => "3", 
			"selectbind" => "3", 
			"selectdo" => "3", 
			"selectempty" => "3", 
			"selecteos" => "3", 
			"selecterror" => "3", 
			"set" => "3", 
			"static" => "3", 
			"step" => "3", 
			"string" => "3", 
			"table" => "3", 
			"then" => "3", 
			"to" => "3", 
			"true" => "3", 
			"until" => "3", 
			"void" => "3", 
			"where" => "3", 
			"wherebind" => "3", 
			"whereused" => "3", 
			"while" => "3", 
			"with" => "3", 
			"after.choice:" => "4", 
			"after.commit.transaction" => "4", 
			"after.delete:" => "4", 
			"after.display:" => "4", 
			"after.display.object:" => "4", 
			"after.field:" => "4", 
			"after.form:" => "4", 
			"after.input:" => "4", 
			"after.program:" => "4", 
			"after.read:" => "4", 
			"after.rewrite:" => "4", 
			"after.skip.delete:" => "4", 
			"after.skip.rewrite:" => "4", 
			"after.skip.write:" => "4", 
			"after.update.db.commit:" => "4", 
			"after.write:" => "4", 
			"after.zoom:" => "4", 
			"after.get.object" => "4", 
			"after.destroy.object" => "4", 
			"after.save.object" => "4", 
			"before.checks:" => "4", 
			"before.choice:" => "4", 
			"before.delete:" => "4", 
			"before.display:" => "4", 
			"before.display.object:" => "4", 
			"before.field:" => "4", 
			"before.form:" => "4", 
			"before.input:" => "4", 
			"before.layout:" => "4", 
			"before.open.object.set" => "4", 
			"before.get.object" => "4", 
			"before.destroy.object" => "4", 
			"before.save.object" => "4", 
			"before.program:" => "4", 
			"before.read:" => "4", 
			"before.rewrite:" => "4", 
			"before.write:" => "4", 
			"before.zoom:" => "4", 
			"check.input:" => "4", 
			"choice.abort.program:" => "4", 
			"choice.add.set:" => "4", 
			"choice.bms:" => "4", 
			"choice.change.frm:" => "4", 
			"choice.change.order:" => "4", 
			"choice.cont.process:" => "4", 
			"choice.create.job:" => "4", 
			"choice.def.find:" => "4", 
			"choice.dupl.occur:" => "4", 
			"choice.end.program:" => "4", 
			"choice.find.data:" => "4", 
			"choice.first.frm:" => "4", 
			"choice.first.set:" => "4", 
			"choice.first.view:" => "4", 
			"choice.get.defaults:" => "4", 
			"choice.global.copy:" => "4", 
			"choice.global.delete:" => "4", 
			"choice.interrupt:" => "4", 
			"choice.last.frm:" => "4", 
			"choice.last.set:" => "4", 
			"choice.last.view:" => "4", 
			"choice.make.resident:" => "4", 
			"choice.mark.delete:" => "4", 
			"choice.mark.occur:" => "4", 
			"choice.modify.set:" => "4", 
			"choice.next.frm:" => "4", 
			"choice.next.halfset:" => "4", 
			"choice.next.set:" => "4", 
			"choice.prev.frm:" => "4", 
			"choice.prev.halfset:" => "4", 
			"choice.prev.set:" => "4", 
			"choice.prev.view:" => "4", 
			"choice.print.data:" => "4", 
			"choice.recover.set:" => "4", 
			"choice.resize.frm:" => "4", 
			"choice.restart.input:" => "4", 
			"choice.run.job:" => "4", 
			"choice.save.defaults:" => "4", 
			"choice.start.chart:" => "4", 
			"choice.start.query:" => "4", 
			"choice.start.set:" => "4", 
			"choice.text.manager:" => "4", 
			"choice.update.db:" => "4", 
			"choice.user.0:" => "4", 
			"choice.user.1:" => "4", 
			"choice.user.2:" => "4", 
			"choice.user.3:" => "4", 
			"choice.user.4:" => "4", 
			"choice.user.5:" => "4", 
			"choice.user.6:" => "4", 
			"choice.user.7:" => "4", 
			"choice.user.8:" => "4", 
			"choice.user.9:" => "4", 
			"choice.zoom:" => "4", 
			"declaration:" => "4", 
			"domain.error:" => "4", 
			"field.all:" => "4", 
			"field.other:" => "4", 
			"form.10:" => "4", 
			"form.11:" => "4", 
			"form.12:" => "4", 
			"form.1:" => "4", 
			"form.2:" => "4", 
			"form.3:" => "4", 
			"form.4:" => "4", 
			"form.5:" => "4", 
			"form.6:" => "4", 
			"form.7:" => "4", 
			"form.8:" => "4", 
			"form.9:" => "4", 
			"form.all:" => "4", 
			"form.other:" => "4", 
			"functions:" => "4", 
			"init.field:" => "4", 
			"init.form:" => "4", 
			"main.table.io:" => "4", 
			"on.choice:" => "4", 
			"on.entry:" => "4", 
			"on.error:" => "4", 
			"on.exit:" => "4", 
			"on.input:" => "4", 
			"read.view:" => "4", 
			"ref.display:" => "4", 
			"ref.input:" => "4", 
			"when.field.changes:" => "4", 
			"zoom.from.all:" => "4", 
			"zoom.from.other:" => "4", 
			"{" => "5", 
			"}" => "5", 
			"(" => "5", 
			")" => "5", 
			"[" => "5", 
			"]" => "5", 
			"+" => "5", 
			"-" => "5", 
			"*" => "5", 
			"//" => "5", 
			"/" => "5", 
			"%" => "5", 
			"&" => "5", 
			"?" => "5", 
			":" => "5", 
			"^" => "5", 
			"!" => "5", 
			"~" => "5", 
			"'" => "5", 
			"<" => "5", 
			">" => "5", 
			"=" => "5", 
			"<>" => "6");

// Special extensions

// Each category can specify a PHP function that returns an altered
// version of the keyword.
        
        

$this->linkscripts    	= array(
			"1" => "donothing", 
			"2" => "donothing", 
			"3" => "donothing", 
			"4" => "donothing", 
			"5" => "donothing", 
			"6" => "donothing");
}


function donothing($keywordin)
{
	return $keywordin;
}

}?>
