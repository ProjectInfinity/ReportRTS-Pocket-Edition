<?php

namespace ProjectInfinity\ReportRTS\util;

class PermissionHandler {

    const isStaff = "reportrts.staff";

    const canReload = "reportrts.command.reload";
    const canReadAll = "reportrts.command.read";
    const canReadSelf = "reportrts.command.self";
    const canOpenTicket = "reportrts.command.open";
    const canCloseTicket = "reportrts.command.close";
    const canClaimTicket = "reportrts.command.claim";
    const canHoldTicket = "reportrts.command.hold";
    const canSeeStaff = "reportrts.command.list";
    const canTeleport = "reportrts.command.teleport";

    const bypassTicketLimit = "reportrts.bypass.ticket";
    const bypassTicketClaim = "reportrts.bypass.claim";
}