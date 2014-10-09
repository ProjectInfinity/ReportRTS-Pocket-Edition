<?php

namespace ProjectInfinity\ReportRTS\util;

class PermissionHandler {

    const isStaff = "reportrts.staff";

    const canReload = "reportrts.command.reload";
    const canReadAll = "reportrts.command.read";
    const canOpenTicket = "reportrts.command.open";
    const canClaimTicket = "reportrts.command.claim";
    const canHoldTicket = "reportrts.command.hold";

    const bypassTicketLimit = "reportrts.bypass.ticket";
    const bypassTicketClaim = "reportrts.bypass.claim";

}